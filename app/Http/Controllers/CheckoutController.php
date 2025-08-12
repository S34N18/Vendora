<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderConfirmation;

class CheckoutController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Display the checkout page
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        // Get cart items with current product details
        $cartItems = $this->getCartItems($cart);
        
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'No valid items in cart');
        }

        // Calculate totals
        $totals = $this->calculateTotals($cartItems);
        
        // Get user data if logged in
        $user = Auth::user();
        
        return view('checkout.index', compact('cartItems', 'totals', 'user'));
    }

    /**
     * Process the checkout form
     */
    public function store(Request $request)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty'
            ], 400);
        }

        // Validate the form
        $validator = $this->validateCheckoutForm($request);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get cart items and verify stock
            $cartItems = $this->getCartItems($cart);
            $stockCheck = $this->verifyStock($cartItems, $cart);
            
            if (!$stockCheck['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $stockCheck['message']
                ], 400);
            }

            // Calculate totals
            $totals = $this->calculateTotals($cartItems);

            // Create the order
            $order = $this->createOrder($request, $totals);

            // Create order items
            $this->createOrderItems($order, $cartItems, $cart);

            // Initiate M-Pesa payment
            $mpesaResponse = $this->initiateMpesaPayment($order, $request->phone);

            if ($mpesaResponse['success']) {
                // Update order with M-Pesa details
                $order->update([
                    'status' => 'payment_initiated',
                    'payment_status' => 'pending',
                    'mpesa_checkout_request_id' => $mpesaResponse['CheckoutRequestID']
                ]);

                DB::commit();

                // Clear the cart
                Session::forget('cart');

                Log::info('Order created and payment initiated', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'checkout_request_id' => $mpesaResponse['CheckoutRequestID']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated successfully. Please enter your M-Pesa PIN.',
                    'order_id' => $order->id,
                    'checkout_request_id' => $mpesaResponse['CheckoutRequestID'],
                    'redirect_url' => route('checkout.success', $order->id),
                    'data' => [
                        'order_id' => $order->id,
                        'checkout_request_id' => $mpesaResponse['CheckoutRequestID'],
                        'phone_number' => $request->phone
                    ]
                ]);
            } else {
                DB::rollback();
                Log::error('Payment initiation failed', [
                    'order_id' => $order->id,
                    'error' => $mpesaResponse['message'] ?? 'Unknown error'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $mpesaResponse['message'] ?? 'Payment initiation failed'
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Checkout error: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during checkout. Please try again.'
            ], 500);
        }
    }

    /**
     * Show order success page
     */
    public function success($orderId)
    {
        $order = Order::with('items.product')->findOrFail($orderId);
        
        // Only allow access to own orders or guest orders
        if ($order->user_id && (!Auth::check() || $order->user_id !== Auth::id())) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }

    /**
     * Check payment status via AJAX
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);
        
        // Check if user has access to this order
        if ($order->user_id && (!Auth::check() || $order->user_id !== Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        Log::info('Payment status check', [
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'order_status' => $order->status
        ]);
        
        return response()->json([
            'success' => true,
            'status' => $order->payment_status,
            'order_status' => $order->status,
            'message' => $this->getStatusMessage($order->payment_status)
        ]);
    }

    /**
     * Get cart items with product details
     */
    private function getCartItems($cart)
    {
        $cartItems = [];
        
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->current_price * $item['quantity']
                ];
            }
        }
        
        return $cartItems;
    }

    /**
     * Calculate order totals
     */
    private function calculateTotals($cartItems)
    {
        $subtotal = collect($cartItems)->sum('subtotal');
        $taxRate = 0.16; // 16% VAT in Kenya
        $taxAmount = $subtotal * $taxRate;
        $shippingAmount = $subtotal >= 5000 ? 0 : 200; // Free shipping over 5000 KSH
        $total = $subtotal + $taxAmount + $shippingAmount;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total' => $total
        ];
    }

    /**
     * Verify stock availability
     */
    private function verifyStock($cartItems, $cart)
    {
        foreach ($cartItems as $item) {
            $productId = $item['product']->id;
            $requestedQty = $cart[$productId]['quantity'];
            
            if ($item['product']->quantity < $requestedQty) {
                return [
                    'success' => false,
                    'message' => "Not enough stock for {$item['product']->name}. Available: {$item['product']->quantity}"
                ];
            }
        }
        
        return ['success' => true];
    }

    /**
     * Create order record
     */
    private function createOrder($request, $totals)
    {
        return Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => Auth::id(),
            'total_amount' => $totals['total'],
            'tax_amount' => $totals['tax_amount'],
            'shipping_amount' => $totals['shipping_amount'],
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'mpesa',
            'shipping_address' => [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'county' => $request->county
            ],
            'billing_address' => [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'county' => $request->county
            ],
            'notes' => $request->notes
        ]);
    }

    /**
     * Create order items
     */
    private function createOrderItems($order, $cartItems, $cart)
    {
        foreach ($cartItems as $item) {
            $productId = $item['product']->id;
            $quantity = $cart[$productId]['quantity'];
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $item['product']->current_price,
                'total' => $item['product']->current_price * $quantity
            ]);

            // Reduce product stock
            $item['product']->decrement('quantity', $quantity);
        }
    }

    /**
     * Initiate M-Pesa payment - IMPROVED VERSION
     */
    private function initiateMpesaPayment($order, $phone)
    {
        // Clean phone number (remove spaces, dashes, etc.)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert to 254 format if needed
        if (substr($cleanPhone, 0, 1) == '0') {
            $cleanPhone = '254' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 3) !== '254') {
            $cleanPhone = '254' . $cleanPhone;
        }

        // Validate phone number format
        if (!preg_match('/^254[17]\d{8}$/', $cleanPhone)) {
            Log::error('Invalid phone number format', [
                'original' => $phone,
                'cleaned' => $cleanPhone
            ]);
            return [
                'success' => false,
                'message' => 'Invalid phone number format. Please use format: 0712345678'
            ];
        }

        try {
            Log::info('Initiating M-Pesa payment', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'phone' => $cleanPhone,
                'amount' => $order->total_amount
            ]);

            $response = $this->mpesaService->stkPush([
                'phone' => $cleanPhone,
                'amount' => (int) round($order->total_amount), // Ensure integer
                'reference' => $order->order_number,
                'description' => "Payment for Order #{$order->order_number}"
            ]);

            Log::info('M-Pesa STK Push Response', [
                'order_id' => $order->id,
                'response' => $response
            ]);

            // Check for successful response
            if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                // Create transaction record for tracking
                MpesaTransaction::create([
                    'order_id' => $order->id,
                    'phone' => $cleanPhone,
                    'amount' => $order->total_amount,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'status' => 'pending'
                ]);

                Log::info('M-Pesa transaction record created', [
                    'order_id' => $order->id,
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);

                return [
                    'success' => true,
                    'CheckoutRequestID' => $response['CheckoutRequestID']
                ];
            } else {
                Log::error('M-Pesa STK Push failed', [
                    'response' => $response,
                    'order_id' => $order->id,
                    'response_code' => $response['ResponseCode'] ?? 'N/A'
                ]);
                
                return [
                    'success' => false,
                    'message' => $response['errorMessage'] ?? 'Payment initiation failed. Please try again.'
                ];
            }

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push exception: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'phone' => $cleanPhone,
                'exception' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment service is currently unavailable. Please try again later.'
            ];
        }
    }

    /**
     * Validate checkout form
     */
    private function validateCheckoutForm($request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^(0|\+254|254)?[17]\d{8}$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'county' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000'
        ], [
            'phone.regex' => 'Please enter a valid Kenyan phone number (e.g., 0712345678 or 0101234567)'
        ]);
    }

    /**
     * Get status message for payment status
     */
    private function getStatusMessage($status)
    {
        $messages = [
            'pending' => 'Waiting for payment confirmation...',
            'paid' => 'Payment completed successfully!',
            'failed' => 'Payment failed. Please try again.',
            'cancelled' => 'Payment was cancelled.',
            'timeout' => 'Payment request timed out. Please try again.'
        ];

        return $messages[$status] ?? 'Unknown payment status';
    }
}