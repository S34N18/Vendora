<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items.product'])
            ->where('user_id', Auth::id());

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

        // Get user order statistics
        $stats = $this->getUserOrderStatistics();

        return view('user.orders.index', compact('orders', 'stats'));
    }

    public function show($id)
    {
        $order = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
            
        return view('user.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->back()->with('error', 'Order cannot be cancelled at this stage.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Order cancelled successfully!');
    }

    public function reorder(Order $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Clear current cart
        session()->forget('cart');

        // Add order items to cart
        $cart = [];
        foreach ($order->items as $item) {
            $cart[$item->product_id] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'image' => $item->product->image ?? 'default.jpg'
            ];
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Items added to cart successfully!');
    }

    public function downloadInvoice(Order $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // You can implement PDF generation here
        // For now, we'll just redirect back
        return redirect()->back()->with('info', 'Invoice download coming soon!');
    }

    private function getUserOrderStatistics()
    {
        $userId = Auth::id();
        
        return [
            'total_orders' => Order::where('user_id', $userId)->count(),
            'pending_orders' => Order::where('user_id', $userId)->where('status', 'pending')->count(),
            'processing_orders' => Order::where('user_id', $userId)->where('status', 'processing')->count(),
            'shipped_orders' => Order::where('user_id', $userId)->where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('user_id', $userId)->where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'total_spent' => Order::where('user_id', $userId)->where('payment_status', 'paid')->sum('total_amount'),
        ];
    }
}