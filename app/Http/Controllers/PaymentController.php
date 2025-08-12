<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MpesaService;
use App\Models\Order;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    public function callback(Request $request)
    {
        // CRITICAL: Log all incoming data for debugging
        $data = $request->all();
        Log::info('M-Pesa Callback Raw Data:', [
            'headers' => $request->headers->all(),
            'body' => $data,
            'ip' => $request->ip(),
            'method' => $request->method()
        ]);

        // Validate that this is actually from Safaricom
        if (!$this->isValidCallback($request)) {
            Log::warning('Invalid M-Pesa callback attempt', [
                'ip' => $request->ip(),
                'data' => $data
            ]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback']);
        }

        try {
            if (isset($data['Body']['stkCallback'])) {
                $callback = $data['Body']['stkCallback'];
                $this->processCallback($callback);
            } else {
                Log::error('M-Pesa callback missing stkCallback structure', $data);
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing error: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
        }

        // Always return success to prevent retries
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function timeout(Request $request)
    {
        $data = $request->all();
        Log::info('M-Pesa Timeout:', $data);
        
        // Handle timeout - mark transactions as failed
        if (isset($data['Body']['stkCallback']['CheckoutRequestID'])) {
            $checkoutRequestId = $data['Body']['stkCallback']['CheckoutRequestID'];
            
            $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'result_desc' => 'Transaction timeout'
                ]);
                
                // Update order status
                if ($transaction->order) {
                    $transaction->order->update([
                        'payment_status' => 'failed',
                        'status' => 'payment_failed'
                    ]);
                }
            }
        }
        
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    private function processCallback($callback)
    {
        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $resultCode = $callback['ResultCode'] ?? null;
        
        if (!$checkoutRequestId) {
            Log::error('M-Pesa callback missing CheckoutRequestID');
            return;
        }

        // Find the transaction - check both tables
        $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->first();
        $order = Order::where('mpesa_checkout_request_id', $checkoutRequestId)->first();
        
        if (!$transaction && !$order) {
            Log::warning('M-Pesa callback for unknown transaction', [
                'checkout_request_id' => $checkoutRequestId
            ]);
            return;
        }

        if ($resultCode === 0) {
            // Payment successful
            $this->handleSuccessfulPayment($callback, $transaction, $order);
        } else {
            // Payment failed
            $this->handleFailedPayment($callback, $transaction, $order);
        }
    }

    private function handleSuccessfulPayment($callback, $transaction, $order)
    {
        $callbackMetadata = collect($callback['CallbackMetadata']['Item'] ?? []);
        
        $receiptNumber = $this->getMetadataValue($callbackMetadata, 'MpesaReceiptNumber');
        $transactionDate = $this->getMetadataValue($callbackMetadata, 'TransactionDate');
        $phoneNumber = $this->getMetadataValue($callbackMetadata, 'PhoneNumber');
        $amount = $this->getMetadataValue($callbackMetadata, 'Amount');

        Log::info('Processing successful M-Pesa payment', [
            'receipt' => $receiptNumber,
            'amount' => $amount,
            'phone' => $phoneNumber
        ]);

        // Update transaction if exists
        if ($transaction) {
            $transaction->update([
                'status' => 'completed',
                'mpesa_receipt_number' => $receiptNumber,
                'transaction_date' => $transactionDate,
                'phone' => $phoneNumber ?: $transaction->phone,
            ]);
            $order = $transaction->order;
        }

        // Update order
        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'mpesa_receipt_number' => $receiptNumber
            ]);

            Log::info('Order payment confirmed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            // Send confirmation email, reduce inventory, etc.
            $this->postPaymentActions($order);
        }
    }

    private function handleFailedPayment($callback, $transaction, $order)
    {
        $resultDesc = $callback['ResultDesc'] ?? 'Payment failed';
        
        Log::info('Processing failed M-Pesa payment', [
            'result_desc' => $resultDesc,
            'result_code' => $callback['ResultCode'] ?? null
        ]);

        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'result_desc' => $resultDesc
            ]);
            $order = $transaction->order;
        }

        if ($order) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'payment_failed'
            ]);
        }
    }

    private function getMetadataValue($collection, $name)
    {
        $item = $collection->firstWhere('Name', $name);
        return $item['Value'] ?? null;
    }

    private function isValidCallback($request)
    {
        // Add your validation logic here
        // Check IP whitelist, headers, etc.
        
        // For now, basic validation
        return $request->hasHeader('content-type') || 
               $request->hasHeader('Content-Type') ||
               !empty($request->all());
    }

    private function postPaymentActions($order)
    {
        // Add any post-payment actions here
        // - Send confirmation email
        // - Update inventory
        // - Trigger webhooks
        // - Send SMS notifications
    }
}