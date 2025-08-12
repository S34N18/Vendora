@extends('layouts.app')
{{-- resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mark's Online Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
</head>
<body>
    <!-- Navigation -->
    <nav class="nav-container">
        <div class="nav-wrapper">
            <div class="nav-content">
                <div class="nav-brand">
                    <h1 class="nav-title">Admin Panel - Vendora Supermarket</h1>
                </div>
                <div class="nav-user-section">
                    <div class="user-info-dropdown">
                        <span class="nav-welcome">Welcome, Admin <span class="nav-username">{{ Auth::user()->name }}</span>!</span>
                        <div class="user-actions">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                👤 User Dashboard
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                🛍️ View Store
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="form-inline logout-form">
                                @csrf
                                <button type="submit" class="btn btn-logout">
                                    🚪 Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Dashboard Overview Card -->
            <div class="dashboard-card">
                <div class="dashboard-card-content">
                    <h2 class="dashboard-title">Admin Dashboard</h2>
                    <div class="admin-info-grid">
                        <div class="admin-info-item">
                            <strong>Name:</strong> {{ Auth::user()->name }}
                        </div>
                        <div class="admin-info-item">
                            <strong>Email:</strong> {{ Auth::user()->email }}
                        </div>
                        <div class="admin-info-item">
                            <strong>Role:</strong> Administrator
                        </div>
                        <div class="admin-info-item">
                            <strong>Last Login:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid mt-6">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <div class="stat-title">Total Orders</div>
                        <div class="stat-value" id="order-count">Loading...</div>
                        <div class="stat-subtitle">All Time</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <div class="stat-title">Total Products</div>
                        <div class="stat-value" id="product-count">Loading...</div>
                        <div class="stat-subtitle">In Stock</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-title">Revenue</div>
                        <div class="stat-value" id="revenue-count">Loading...</div>
                        <div class="stat-subtitle">Total Paid</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-title">Pending Orders</div>
                        <div class="stat-value" id="pending-count">Loading...</div>
                        <div class="stat-subtitle">Need Attention</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3 class="dashboard-title">Quick Actions</h3>
                <div class="actions-grid">
                    <a href="{{ route('admin.orders.index') }}" class="action-card">
                        <div class="action-icon">📋</div>
                        <div class="action-content">
                            <div class="action-title">Manage Orders</div>
                            <div class="action-description">View, edit, and process customer orders</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="action-card">
                        <div class="action-icon">📦</div>
                        <div class="action-content">
                            <div class="action-title">Manage Products</div>
                            <div class="action-description">View, add, edit, and delete products</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="action-card">
                        <div class="action-icon">➕</div>
                        <div class="action-content">
                            <div class="action-title">Add Product</div>
                            <div class="action-description">Add new products to your store</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.orders.create') }}" class="action-card">
                        <div class="action-icon">🛒</div>
                        <div class="action-content">
                            <div class="action-title">Create Order</div>
                            <div class="action-description">Manually create a new order</div>
                        </div>
                    </a>
                    <a href="{{ route('dashboard') }}" class="action-card">
                        <div class="action-icon">👤</div>
                        <div class="action-content">
                            <div class="action-title">User View</div>
                            <div class="action-description">See your store from customer perspective</div>
                        </div>
                    </a>
                    <a href="#" class="action-card disabled">
                        <div class="action-icon">👥</div>
                        <div class="action-content">
                            <div class="action-title">Manage Users</div>
                            <div class="action-description">Manage customer accounts</div>
                            <small>Coming Soon</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="recent-orders">
                <h3 class="dashboard-title">Recent Orders</h3>
                <div class="orders-table-container">
                    <table class="orders-table" id="recent-orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="loading">Loading recent orders...</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="view-all-orders">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">View All Orders</a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="system-status">
                <h3 class="dashboard-title">System Status</h3>
                <div class="status-grid">
                    <div class="status-card status-good">
                        <div class="status-icon">✅</div>
                        <div class="status-content">
                            <div class="status-title">Application</div>
                            <div class="status-value">Online</div>
                        </div>
                    </div>
                    <div class="status-card status-good">
                        <div class="status-icon">✅</div>
                        <div class="status-content">
                            <div class="status-title">Database</div>
                            <div class="status-value">Connected</div>
                        </div>
                    </div>
                    <div class="status-card status-good">
                        <div class="status-icon">✅</div>
                        <div class="status-content">
                            <div class="status-title">Orders</div>
                            <div class="status-value">Active</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3 class="dashboard-title">Recent Activity</h3>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">🚪</div>
                        <div class="activity-content">
                            <div class="activity-title">Admin logged in</div>
                            <div class="activity-time">Just now</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">📋</div>
                        <div class="activity-content">
                            <div class="activity-title">Order management system activated</div>
                            <div class="activity-time">Ready to process orders</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">👤</div>
                        <div class="activity-content">
                            <div class="activity-title">Admin account created</div>
                            <div class="activity-time">{{ Auth::user()->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        // Add logout confirmation
        document.querySelector('.logout-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });

        // Load dashboard data
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            loadRecentOrders();
        });

        async function loadDashboardStats() {
            try {
                // You can replace this with actual API calls to your Laravel backend
                const response = await fetch('/api/admin/dashboard-stats');
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('order-count').textContent = data.total_orders || '0';
                    document.getElementById('product-count').textContent = data.total_products || '0';
                    document.getElementById('revenue-count').textContent = '$' + (data.total_revenue || '0.00');
                    document.getElementById('pending-count').textContent = data.pending_orders || '0';
                } else {
                    throw new Error('Failed to load stats');
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
                // Fallback to showing 0 values
                document.getElementById('order-count').textContent = '0';
                document.getElementById('product-count').textContent = '0';
                document.getElementById('revenue-count').textContent = '$0.00';
                document.getElementById('pending-count').textContent = '0';
            }
        }

        async function loadRecentOrders() {
            try {
                const response = await fetch('/api/admin/recent-orders');
                if (response.ok) {
                    const orders = await response.json();
                    displayRecentOrders(orders);
                } else {
                    throw new Error('Failed to load recent orders');
                }
            } catch (error) {
                console.error('Error loading recent orders:', error);
                document.querySelector('#recent-orders-table tbody').innerHTML = 
                    '<tr><td colspan="6" class="loading">No recent orders found</td></tr>';
            }
        }

        function displayRecentOrders(orders) {
            const tbody = document.querySelector('#recent-orders-table tbody');
            
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="loading">No recent orders found</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td>${order.order_number}</td>
                    <td>${order.customer_name}</td>
                    <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
                    <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                    <td>
                        <a href="/admin/orders/${order.id}" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
            `).join('');
        }

        // Add some sample data for demonstration if API is not available
        setTimeout(() => {
            if (document.getElementById('order-count').textContent === 'Loading...') {
                document.getElementById('order-count').textContent = '0';
                document.getElementById('product-count').textContent = '0';
                document.getElementById('revenue-count').textContent = '$0.00';
                document.getElementById('pending-count').textContent = '0';
                
                document.querySelector('#recent-orders-table tbody').innerHTML = 
                    '<tr><td colspan="6" class="loading">No recent orders found</td></tr>';
            }
        }, 3000);
    </script>
</body>
</html>