@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Orders Management</h1>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <h5>Total Orders</h5>
                            <h3>{{ $stats['total_orders'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <h5>Pending</h5>
                            <h3>{{ $stats['pending_orders'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <h5>Processing</h5>
                            <h3>{{ $stats['processing_orders'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <h5>Shipped</h5>
                            <h3>{{ $stats['shipped_orders'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <h5>Delivered</h5>
                            <h3>{{ $stats['delivered_orders'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <h5>Revenue</h5>
                            <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.index') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="payment_status" class="form-control">
                                    <option value="">All Payment Status</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search orders...">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-3">
                <a href="{{ route('admin.orders.create') }}" class="btn btn-success">Create New Order</a>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->user->name }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $order->payment_status_badge }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @if($order->canBeCancelled())
                                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection