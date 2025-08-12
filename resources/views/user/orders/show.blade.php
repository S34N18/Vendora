{{-- resources/views/user/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' - Vendora Supermarket')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-orders.css') }}">
@endpush

@section('content')
<div class="order-details-wrapper">
    <div class="order-header">
        <div class="header-content">
            <h1>Order #{{ $order->order_number }}</h1>
            <p>Placed on {{ $order->created_at->format('F j, Y - g:i A') }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('user.orders.index') }}" class="btn btn-secondary">← Back to Orders</a>
            
            @if($order->canBeCancelled())
            <form method="POST" action="{{ route('user.orders.cancel', $order) }}" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to cancel this order?')">
                    Cancel Order
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Order Status -->
    <div class="status-section">
        <div class="status-info">
            <h3>Order Status</h3>
            <div class="status-badges">
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
                <span class="payment-badge payment-{{ $order->payment_status }}">
                    Payment: {{ ucfirst($order->payment_status) }}
                </span>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="order-timeline">
            <div class="timeline-item {{ $order->status != 'cancelled' ? 'completed' : '' }}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Order Placed</h4>
                    <p>{{ $order->created_at->format('M d, Y - g:i A') }}</p>
                </div>
            </div>

            <div class="timeline-item {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'completed' : '' }}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Processing</h4>
                    <p>{{ $order->status == 'processing' || in_array($order->status, ['shipped', 'delivered']) ? 'Order is being processed' : 'Pending' }}</p>
                </div>
            </div>

            <div class="timeline-item {{ in_array($order->status, ['shipped', 'delivered']) ? 'completed' : '' }}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Shipped</h4>
                    <p>{{ $order->shipped_at ? $order->shipped_at->format('M d, Y - g:i A') : 'Not shipped yet' }}</p>
                </div>
            </div>

            <div class="timeline-item {{ $order->status == 'delivered' ? 'completed' : '' }}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Delivered</h4>
                    <p>{{ $order->delivered_at ? $order->delivered_at->format('M d, Y - g:i A') : 'Not delivered yet' }}</p>
                </div>
            </div>

            @if($order->status == 'cancelled')
            <div class="timeline-item cancelled">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Cancelled</h4>
                    <p>Order was cancelled</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Order Items -->
    <div class="order-items-section">
        <h3>Order Items</h3>
        <div class="items-list">
            @foreach($order->items as $item)
            <div class="item-card">
                <div class="item-image">
                    <img src="{{ asset('storage/' . ($item->product->image ?? 'default.jpg')) }}" 
                         alt="{{ $item->product->name }}">
                </div>
                <div class="item-details">
                    <h4>{{ $item->product->name }}</h4>
                    <p class="item-description">{{ $item->product->description ?? 'No description available' }}</p>
                    <div class="item-price">
                        <span class="quantity">Qty: {{ $item->quantity }}</span>
                        <span class="price">× ${{ number_format($item->price, 2) }}</span>
                        <span class="total">= ${{ number_format($item->total, 2) }}</span>
                    </div>
                </div>
                <div class="item-actions">
                    <a href="{{ route('products.show', $item->product) }}" class="btn btn-sm btn-outline">View Product</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Order Summary -->
    <div class="order-summary-section">
        <div class="summary-grid">
            <!-- Address Information -->
            <div class="address-info">
                <h3>Shipping Address</h3>
                <div class="address-card">
                    <p><strong>{{ $order->shipping_address['name'] ?? 'N/A' }}</strong></p>
                    <p>{{ $order->shipping_address['address'] ?? 'N/A' }}</p>
                    <p>{{ $order->shipping_address['city'] ?? 'N/A' }}, {{ $order->shipping_address['postal_code'] ?? 'N/A' }}</p>
                    <p>{{ $order->shipping_address['country'] ?? 'N/A' }}</p>
                </div>

                <h3>Billing Address</h3>
                <div class="address-card">
                    <p><strong>{{ $order->billing_address['name'] ?? 'N/A' }}</strong></p>
                    <p>{{ $order->billing_address['address'] ?? 'N/A' }}</p>
                    <p>{{ $order->billing_address['city'] ?? 'N/A' }}, {{ $order->billing_address['postal_code'] ?? 'N/A' }}</p>
                    <p>{{ $order->billing_address['country'] ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Order Total -->
            <div class="order-total">
                <h3>Order Summary</h3>
                <div class="total-breakdown">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>${{ number_format($order->total_amount - $order->tax_amount - $order->shipping_amount, 2) }}</span>
                    </div>
                    @if($order->tax_amount > 0)
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($order->shipping_amount > 0)
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>${{ number_format($order->shipping_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="total-row final-total">
                        <span><strong>Total:</strong></span>
                        <span><strong>${{ number_format($order->total_amount, 2) }}</strong></span>
                    </div>
                </div>

                <div class="payment-info">
                    <h4>Payment Method</h4>
                    <p>{{ ucfirst($order->payment_method) }}</p>
                </div>

                @if($order->notes)
                <div class="order-notes">
                    <h4>Order Notes</h4>
                    <p>{{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Order Actions -->
    <div class="order-actions-section">
        @if($order->status == 'delivered')
        <form method="POST" action="{{ route('user.orders.reorder', $order) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">Reorder Items</button>
        </form>
        @endif

        <a href="{{ route('user.orders.download-invoice', $order) }}" class="btn btn-secondary">Download Invoice</a>
    </div>
</div>
@endsection