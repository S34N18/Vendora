@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Success Header -->
            <div class="text-center mb-4">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                </div>
                <h2 class="text-success">Order Confirmed!</h2>
                <p class="lead text-muted">Thank you for your order. We'll send you a confirmation email shortly.</p>
            </div>

            <!-- Order Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-light text-success">{{ ucfirst($order->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Order Status</h6>
                            <span class="badge bg-{{ $order->status === 'paid' ? 'success' : 'warning' }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Payment Status</h6>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} fs-6">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Customer Details</h6>
                            <address class="mb-0">
                                <strong>{{ $order->shipping_address['name'] }}</strong><br>
                                {{ $order->shipping_address['email'] }}<br>
                                {{ $order->shipping_address['phone'] }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Delivery Address</h6>
                            <address class="mb-0">
                                {{ $order->shipping_address['address'] }}<br>
                                {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['county'] }}
                            </address>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6 class="text-muted mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                                     alt="{{ $item->product->name }}" 
                                                     class="img-thumbnail me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                            <span>{{ $item->product->name ?? 'Product' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">KSh {{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">KSh {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <th class="text-end">KSh {{ number_format($order->total_amount - $order->tax_amount - $order->shipping_amount, 2) }}</th>
                                </tr>
                                @if($order->tax_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">VAT (16%):</th>
                                    <th class="text-end">KSh {{ number_format($order->tax_amount, 2) }}</th>
                                </tr>
                                @endif
                                @if($order->shipping_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Shipping:</th>
                                    <th class="text-end">KSh {{ number_format($order->shipping_amount, 2) }}</th>
                                </tr>
                                @else
                                <tr>
                                    <th colspan="3" class="text-end">Shipping:</th>
                                    <th class="text-end text-success">FREE</th>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end text-primary">KSh {{ number_format($order->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- What's Next -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">What's Next?</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                                <h6>Confirmation Email</h6>
                                <p class="small text-muted">You'll receive an order confirmation email within a few minutes.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="text-info mb-2">
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                                <h6>Order Processing</h6>
                                <p class="small text-muted">We'll prepare your order and notify you when it's ready to ship.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="text-success mb-2">
                                    <i class="fas fa-truck fa-2x"></i>
                                </div>
                                <h6>Delivery</h6>
                                <p class="small text-muted">Your order will be delivered to your specified address.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="fas fa-home me-2"></i>Continue Shopping
                </a>
                <div class="d-flex gap-2">
                    @auth
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info">
                        <i class="fas fa-eye me-2"></i>Track Order
                    </a>
                    @endauth
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                </div>
            </div>

            <!-- Support Information -->
            <div class="alert alert-light mt-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="alert-heading mb-1">Need Help?</h6>
                        <p class="mb-0">Contact our customer support if you have any questions about your order.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="mailto:support@example.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    .btn, .alert, .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container {
        max-width: none !important;
        padding: 0 !important;
    }
}
</style>
@endsection