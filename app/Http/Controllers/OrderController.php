<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // ADMIN METHODS
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(15);

        // Get statistics
        $stats = $this->getOrderStatistics();

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Update timestamps based on status
        if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
            $order->shipped_at = now();
        }

        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $order->delivered_at = now();
        }

        $order->update(['status' => $newStatus]);

        return redirect()->back()->with('success', 'Order status updated successfully!');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order->update(['payment_status' => $request->payment_status]);

        return redirect()->back()->with('success', 'Payment status updated successfully!');
    }

    public function addNote(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $order->update(['notes' => $request->notes]);

        return redirect()->back()->with('success', 'Order note added successfully!');
    }

    public function create()
    {
        $users = User::all();
        $products = Product::all();
        return view('admin.orders.create', compact('users', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'billing_address' => 'required|array',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $orderItems = [];

            // Calculate total and prepare order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $quantity = $item['quantity'];
                $price = $product->price;
                $total = $price * $quantity;

                $totalAmount += $total;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ];
            }

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user_id,
                'total_amount' => $totalAmount,
                'tax_amount' => 0, // You can calculate tax here
                'shipping_amount' => 0, // You can calculate shipping here
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully!');
    }

    public function destroy(Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()->back()->with('error', 'Order cannot be deleted at this stage.');
        }

        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully!');
    }

    // USER METHODS
    public function userIndex(Request $request)
    {
        $query = Auth::user()->orders()->with(['items.product']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('order_number', 'LIKE', "%{$search}%");
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(10);

        // Get user-specific statistics
        $stats = $this->getUserOrderStatistics(Auth::user());

        return view('user.orders.index', compact('orders', 'stats'));
    }

    public function userShow($id)
    {
        $order = Auth::user()->orders()->with(['items.product'])->findOrFail($id);
        return view('user.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Make sure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$order->canBeCancelled()) {
            return redirect()->back()->with('error', 'Order cannot be cancelled at this stage.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Order cancelled successfully!');
    }

    public function reorder(Order $order)
    {
        // Make sure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Clear existing cart
        session()->forget('cart');

        // Add order items to cart
        $cart = [];
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product) { // Make sure product still exists
                $cart[$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price, // Use current price
                    'quantity' => $item->quantity,
                    'image' => $product->image,
                ];
            }
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Items from order #' . $order->order_number . ' have been added to your cart!');
    }

    public function downloadInvoice(Order $order)
    {
        // Make sure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // You can implement PDF generation here
        // For now, we'll just redirect back with a message
        return redirect()->back()->with('info', 'Invoice download feature coming soon!');
    }

    // HELPER METHODS
    private function getOrderStatistics()
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_payments' => Order::where('payment_status', 'pending')->sum('total_amount'),
        ];
    }

    private function getUserOrderStatistics(User $user)
    {
        return [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'processing_orders' => $user->orders()->where('status', 'processing')->count(),
            'shipped_orders' => $user->orders()->where('status', 'shipped')->count(),
            'delivered_orders' => $user->orders()->where('status', 'delivered')->count(),
            'cancelled_orders' => $user->orders()->where('status', 'cancelled')->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
        ];
    }

    // API METHODS (for admin dashboard)
    public function getDashboardStats()
    {
        $stats = $this->getOrderStatistics();
        return response()->json($stats);
    }

    public function getRecentOrders()
    {
        $orders = Order::with(['user', 'items.product'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json($orders);
    }
}