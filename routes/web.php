<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;

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
Route::post('/api/mpesa/callback', [PaymentController::class, 'callback'])->name('mpesa.callback');
Route::post('/api/mpesa/timeout', [PaymentController::class, 'timeout'])->name('mpesa.timeout');

// Additional M-Pesa routes that DO require authentication (for users checking status)
Route::middleware(['auth'])->group(function () {
    Route::post('/mpesa/initiate-payment', [PaymentController::class, 'initiatePayment'])->name('mpesa.initiate');
    Route::get('/mpesa/check-status', [PaymentController::class, 'checkPaymentStatus'])->name('mpesa.check-status');
});

// Dynamic product show route (keep this after all /products/* routes)
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// ========== MPESA DEBUGGING ROUTES START ==========

// Authentication routes
require __DIR__.'/auth.php';