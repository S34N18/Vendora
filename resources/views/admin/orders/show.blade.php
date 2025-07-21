@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Order Details - {{ $order->order_number }}</h1>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
            </div>

            <!-- Order Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Order Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge {{ $order->status_badge }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Status:</strong></td>
                                    <td>
                                        <span class="badge {{ $order->payment_status_badge }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ $order->payment_method }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($order->shipped_at)
                                <tr>
                                    <td><strong>Shipped:</strong></td>
                                    <td>{{ $order->shipped_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                                @if($order->delivered_at)
                                <tr>
                                    <td><strong>Delivered:</strong></td>
                                    <td>{{ $order->delivered_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $order->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $order->user->email }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Shipping Address</h5>
                        </div>
                        <div class="card-body">
                            <address>
                                {{ $order->shipping_address['name'] }}<br>
                                {{ $order->shipping_address['address'] }}<br>
                                {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['postal_code'] }}<br>
                                {{ $order->shipping_address['country'] }}
                            </address>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Billing Address</h5>
                        </div>
                        <div class="card-body">
                            <address>
                                {{ $order->billing_address['name'] }}<br>
                                {{ $order->billing_address['address'] }}<br>
                                {{ $order->billing_address['city'] }}, {{ $order->billing_address['postal_code'] }}<br>
                                {{ $order->billing_address['country'] }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Order Items</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Subtotal:</th>
                                        <th>${{ number_format($order->total_amount - $order->tax_amount - $order->shipping_amount, 2) }}</th>
                                    </tr>
                                    @if($order->tax_amount > 0)
                                    <tr>
                                        <th colspan="3">Tax:</th>
                                        <th>${{ number_format($order->tax_amount, 2) }}</th>
                                    </tr>
                                    @endif
                                    @if($order->shipping_amount > 0)
                                    <tr>
                                        <th colspan="3">Shipping:</th>
                                        <th>${{ number_format($order->shipping_amount, 2) }}</th>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th colspan="3">Total:</th>
                                        <th>${{ number_format($order->total_amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Update Forms -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Update Order Status</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Update Payment Status</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.orders.update-payment-status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <select name="payment_status" class="form-control">
                                        <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Payment Status</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Add Order Notes</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.orders.add-note', $order) }}">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Add notes...">{{ $order->notes }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Notes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Notes -->
            @if($order->notes)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Order Notes</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ $order->notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection