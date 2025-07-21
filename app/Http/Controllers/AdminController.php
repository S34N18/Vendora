<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get recent orders with user information
        $orders = Order::with('user')->latest()->take(10)->get();
        
        // Get dashboard statistics
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),            'total_users' => User::count(),
            'total_products' => Product::count(),
        ];
        
        return view('admin.dashboard', compact('orders', 'stats'));
    }
    
    // You can add more admin methods here as you expand
    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }
    
    public function products()
    {
        $products = Product::latest()->paginate(20);
        return view('admin.products.index', compact('products'));
    }
}