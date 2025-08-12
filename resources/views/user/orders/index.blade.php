    {{-- resources/views/user/orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Orders - Vendora Supermarket')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-orders.css') }}">
@endpush

@section('content')
<div class="orders-wrapper">
    <div class="orders-header">
        <h1>My Orders</h1>
        <p>Track and manage your order history</p>
    </div>

    <!-- Order Statistics -->
    <div class="order-stats">
        <div class="stat-item">
            <span class="stat-number">{{ $stats['total_orders'] }}</span>
            <span class="stat-label">Total Orders</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $stats['pending_orders'] }}</span>
            <span class="stat-label">Pending</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $stats['delivered_orders'] }}</span>
            <span class="stat-label">Delivered</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">${{ number_format($stats['total_spent'], 2) }}</span>
            <span class="stat-label">Total Spent</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" action="{{ route('user.orders.index') }}" class="filters-form">
            <div class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" placeholder="Order number..." value="{{ request('search') }}">
            </div>

            <div class="filter-group">
                <label for="date_from">From:</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="filter-group">
                <label for="date_to">To:</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('user.orders.index') }}" class="btn btn-secondary">Clear</a>
        </form>
    </div>

    <!-- Orders List -->
    <div class="orders-list">
        @if($orders->count() > 0)
            @foreach($orders as $order)
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3>Order #{{ $order->order_number }}</h3>
                        <p class="order-date">{{ $order->created_at->format('M d, Y - g:i A') }}</p>
                    </div>
                    <div class="order-status">
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        <span class="payment-badge payment-{{ $order->payment_status }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>

                <div class="order-items">
                    @foreach($order->items->take(3) as $item)
                    <div class="order-item">
                        <img src="{{ asset('storage/' . ($item->product->image ?? 'default.jpg')) }}" 
                             alt="{{ $item->product->name }}" class="item-image">
                        <div class="item-info">
                            <h4>{{ $item->product->name }}</h4>
                            <p>Qty: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                    
                    @if($order->items->count() > 3)
                    <div class="more-items">
                        <p>+{{ $order->items->count() - 3 }} more items</p>
                    </div>
                    @endif
                </div>

                <div class="order-footer">
                    <div class="order-total">
                        <strong>Total: ${{ number_format($order->total_amount, 2) }}</strong>
                    </div>
                    <div class="order-actions">
                        <a href="{{ route('user.orders.show', $order) }}" class="btn btn-primary">View Details</a>
                        
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

                        @if($order->status == 'delivered')
                        <form method="POST" action="{{ route('user.orders.reorder', $order) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Reorder</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @else
            <div class="no-orders">
                <div class="no-orders-icon">📋</div>
                <h3>No Orders Found</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
            </div>
        @endif
    </div>
</div>
@endsection