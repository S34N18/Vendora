<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\CheckoutController;

//landing page accessed by everyone
Route::get('/', function () {
    return view('home');
});

// USER DASHBOARD ROUTE - accessible to all authenticated users 
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Debug: Log user info
    \Log::info('Dashboard Route - User Info:', [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_role' => $user->role,
        'role_check' => $user->role === 'admin',
    ]);
    
    // If user is admin, redirect to admin dashboard
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    
    return view('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

// Cart routes
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update.put');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// USER ORDER ROUTES - authenticated users only
Route::middleware(['auth'])->group(function () {
    Route::prefix('my-orders')->name('user.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'userIndex'])->name('index');
        Route::get('/{order}', [OrderController::class, 'userShow'])->name('show');
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/reorder', [OrderController::class, 'reorder'])->name('reorder');
        Route::get('/{order}/download-invoice', [OrderController::class, 'downloadInvoice'])->name('download-invoice');
    });
});

// ADMIN ROUTES - All admin-only routes with proper middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Order Management
    Route::resource('orders', OrderController::class);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
    Route::patch('orders/{order}/notes', [OrderController::class, 'addNote'])->name('orders.add-note');
    
    // Admin Product Management
    Route::get('/products', [ProductController::class, 'adminIndex'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update.patch');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    
    // Admin User Management (if needed)
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
});

// API routes for admin dashboard
Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
    Route::get('/dashboard-stats', [OrderController::class, 'getDashboardStats'])->name('api.admin.dashboard-stats');
    Route::get('/recent-orders', [OrderController::class, 'getRecentOrders'])->name('api.admin.recent-orders');
});

// CHECKOUT ROUTES - authenticated users only
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::post('/process', [CheckoutController::class, 'store'])->name('process');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    Route::post('/payment-status', [CheckoutController::class, 'checkPaymentStatus'])->name('payment-status');
});

// MPESA CALLBACK ROUTES - NO AUTHENTICATION MIDDLEWARE!
// These endpoints are called by Safaricom's servers, not by authenticated users
Route::post('/mpesa/callback', [PaymentController::class, 'callback'])->name('mpesa.callback');
Route::post('/mpesa/timeout', [PaymentController::class, 'timeout'])->name('mpesa.timeout');

// Additional M-Pesa routes that DO require authentication (for users checking status)
Route::middleware(['auth'])->group(function () {
    Route::post('/mpesa/initiate-payment', [PaymentController::class, 'initiatePayment'])->name('mpesa.initiate');
    Route::get('/mpesa/check-status', [PaymentController::class, 'checkPaymentStatus'])->name('mpesa.check-status');
});

// Dynamic product show route (keep this after all /products/* routes)
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// ========== MPESA DEBUGGING ROUTES START ==========
// Use these routes to test your M-Pesa integration

Route::get('/test-mpesa-callback-success', function () {
    // Simulate a successful M-Pesa callback
    $testData = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => 'test-merchant-123',
                'CheckoutRequestID' => 'ws_CO_test12345',
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 1000],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'TEST123456'],
                        ['Name' => 'TransactionDate', 'Value' => '20250720140530'],
                        ['Name' => 'PhoneNumber', 'Value' => '254712345678']
                    ]
                ]
            ]
        ]
    ];
    
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = new \App\Http\Controllers\MpesaController();
    $response = $controller->callback($request);
    
    return "Callback test completed. Check logs for details. Response: " . $response->getContent();
});

Route::get('/test-mpesa-callback-failed', function () {
    // Simulate a failed M-Pesa callback
    $testData = [
        'Body' => [
            'stkCallback' => [
                'CheckoutRequestID' => 'ws_CO_test12345',
                'ResultCode' => 1032,
                'ResultDesc' => 'Request cancelled by user'
            ]
        ]
    ];
    
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = new \App\Http\Controllers\MpesaController();
    $response = $controller->callback($request);
    
    return "Failed callback test completed. Check logs for details. Response: " . $response->getContent();
});

Route::get('/test-callback-url', function () {
    // Test if the callback URL is accessible
    $callbackUrl = url('/mpesa/callback');
    $timeoutUrl = url('/mpesa/timeout');
    
    return response()->json([
        'callback_url' => $callbackUrl,
        'timeout_url' => $timeoutUrl,
        'server_ip' => request()->server('SERVER_ADDR'),
        'accessible' => true,
        'timestamp' => now()
    ]);
});

Route::get('/mpesa-logs', function () {
    // Show recent M-Pesa related logs
    $logFile = storage_path('logs/laravel.log');
    
    if (!file_exists($logFile)) {
        return 'No log file found';
    }
    
    $logs = file_get_contents($logFile);
    $mpesaLogs = collect(explode("\n", $logs))
        ->filter(function ($line) {
            return str_contains(strtolower($line), 'mpesa') || 
                   str_contains(strtolower($line), 'm-pesa') ||
                   str_contains(strtolower($line), 'callback') ||
                   str_contains(strtolower($line), 'checkout');
        })
        ->take(-50) // Last 50 lines
        ->join("\n");
    
    return response($mpesaLogs)->header('Content-Type', 'text/plain');
});

Route::get('/create-test-order', function () {
    // Create a test order for M-Pesa testing
    if (!auth()->check()) {
        return 'Please login first';
    }
    
    $order = \App\Models\Order::create([
        'order_number' => 'TEST-' . time(),
        'user_id' => auth()->id(),
        'total_amount' => 100.00, // 100 KES for testing
        'tax_amount' => 16.00,
        'shipping_amount' => 0,
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'mpesa',
        'shipping_address' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '254712345678',
            'address' => 'Test Address',
            'city' => 'Nairobi',
            'county' => 'Nairobi'
        ],
        'billing_address' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '254712345678',
            'address' => 'Test Address',
            'city' => 'Nairobi',
            'county' => 'Nairobi'
        ]
    ]);
    
    return "Test order created: ID {$order->id}, Number: {$order->order_number}. You can now test checkout with this order.";
});

Route::get('/check-mpesa-service', function () {
    // Test if MpesaService is working
    try {
        $service = app(\App\Services\MpesaService::class);
        return response()->json([
            'service_loaded' => true,
            'service_class' => get_class($service),
            'methods' => get_class_methods($service),
            'config_check' => [
                'consumer_key' => config('mpesa.consumer_key') ? 'Set' : 'Not set',
                'consumer_secret' => config('mpesa.consumer_secret') ? 'Set' : 'Not set',
                'passkey' => config('mpesa.passkey') ? 'Set' : 'Not set',
                'shortcode' => config('mpesa.shortcode') ?: 'Not set',
                'environment' => config('mpesa.environment') ?: 'Not set'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'service_loaded' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Check recent transactions
Route::get('/mpesa-transactions', function () {
    $transactions = \App\Models\MpesaTransaction::latest()->take(10)->get();
    $orders = \App\Models\Order::whereNotNull('mpesa_checkout_request_id')->latest()->take(10)->get();
    
    return response()->json([
        'recent_transactions' => $transactions,
        'recent_orders_with_mpesa' => $orders
    ]);
})->middleware('auth');

// ========== MPESA DEBUGGING ROUTES END ==========

// ========== ORIGINAL DEBUG ROUTES (KEEP FOR NOW) ==========
Route::get('/debug-role', function () {
    if (auth()->check()) {
        $user = auth()->user();
        dd([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'role_type' => gettype($user->role),
            'role_length' => strlen($user->role),
            'role_raw' => var_export($user->role, true),
            'is_admin_check' => $user->role === 'admin',
            'is_administrator_check' => $user->role === 'Administrator',
        ]);
    } else {
        dd('User not authenticated');
    }
})->middleware('auth');

Route::get('/simple-test', function () {
    return 'This works!';
});

// ========== END ORIGINAL DEBUG ROUTES ==========

// Authentication routes
require __DIR__.'/auth.php';