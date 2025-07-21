{{-- resources/views/user/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'My Account - Mark\'s Online Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
@endpush

@section('content')

<div class="dashboard-wrapper">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <h1>Welcome back, {{ Auth::user()->name }}!</h1>
        <p>Manage your account and view your shopping activity</p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🛒</div>
            <div class="stat-content">
                <h3>Cart Items</h3>
                <p class="stat-number" id="cart-count">{{ session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3>Orders</h3>
                <p class="stat-number">{{ Auth::user()->orders->count() }}</p>
                <small>Total Orders</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>Total Spent</h3>
                <p class="stat-number">${{ number_format(Auth::user()->orders->where('payment_status', 'paid')->sum('total_amount'), 2) }}</p>
                <small>All Time</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🚚</div>
            <div class="stat-content">
                <h3>Pending Orders</h3>
                <p class="stat-number">{{ Auth::user()->orders->where('status', 'pending')->count() }}</p>
                <small>Awaiting Processing</small>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="{{ route('products.index') }}" class="action-card">
                <div class="action-icon">🛍️</div>
                <div class="action-content">
                    <h3>Browse Products</h3>
                    <p>Discover our amazing products</p>
                </div>
            </a>

            <a href="{{ route('cart.index') }}" class="action-card">
                <div class="action-icon">🛒</div>
                <div class="action-content">
                    <h3>View Cart</h3>
                    <p>Check your shopping cart</p>
                </div>
            </a>

            <a href="{{ route('profile.edit') }}" class="action-card">
                <div class="action-icon">👤</div>
                <div class="action-content">
                    <h3>Edit Profile</h3>
                    <p>Update your account information</p>
                </div>
            </a>

            <a href="{{ route('user.orders.index') }}" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-content">
                    <h3>Order History</h3>
                    <p>View your past orders</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Orders -->
    @if(Auth::user()->orders->count() > 0)
    <div class="recent-orders">
        <h2>Recent Orders</h2>
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(Auth::user()->orders->take(5) as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="status-badge status-{{ $order->status }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <a href="{{ route('user.orders.show', $order) }}" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('user.orders.index') }}" class="btn btn-primary">View All Orders</a>
        </div>
    </div>
    @endif

    <!-- Account Information -->
    <div class="account-info">
        <h2>Account Information</h2>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-header">
                    <h3>Personal Details</h3>
                    <a href="{{ route('profile.edit') }}" class="edit-link">Edit</a>
                </div>
                <div class="info-content">
                    <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p><strong>Member Since:</strong> {{ Auth::user()->created_at->format('F j, Y') }}</p>
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="info-content">
                    <p><strong>Total Orders:</strong> {{ Auth::user()->orders->count() }}</p>
                    <p><strong>Completed Orders:</strong> {{ Auth::user()->orders->where('status', 'delivered')->count() }}</p>
                    <p><strong>Total Spent:</strong> ${{ number_format(Auth::user()->orders->where('payment_status', 'paid')->sum('total_amount'), 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
        <h2>Recent Activity</h2>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon">🛒</div>
                <div class="activity-content">
                    <p>Cart items: <span id="activity-cart-count">{{ session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}</span></p>
                    <small>Current session</small>
                </div>
            </div>
            
            @if(Auth::user()->orders->count() > 0)
            <div class="activity-item">
                <div class="activity-icon">📋</div>
                <div class="activity-content">
                    <p>Last order: {{ Auth::user()->orders->first()->order_number }}</p>
                    <small>{{ Auth::user()->orders->first()->created_at->diffForHumans() }}</small>
                </div>
            </div>
            @endif
            
            <div class="activity-item">
                <div class="activity-icon">👤</div>
                <div class="activity-content">
                    <p>Account created</p>
                    <small>{{ Auth::user()->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('cart-count').textContent = data.count;
            document.getElementById('activity-cart-count').textContent = data.count;
        })
        .catch(error => console.error('Error fetching cart count:', error));
});
</script>
@endsection