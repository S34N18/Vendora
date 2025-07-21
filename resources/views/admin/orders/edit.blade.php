@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Order - {{ $order->order_number }}</h1>
                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">Back to Order Details</a>
            </div>

            <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Customer Selection -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="user_id">Select Customer</label>
                                    <select name="user_id" id="user_id" class="form-control" required>
                                        <option value="">Choose a customer...</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $order->user_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value="">Choose payment method...</option>
                                        <option value="credit_card" {{ $order->payment_method == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                        <option value="debit_card" {{ $order->payment_method == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                        <option value="paypal" {{ $order->payment_method == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                        <option value="bank_transfer" {{ $order->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="cash_on_delivery" {{ $order->payment_method == 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div id="order-items">
                            @foreach($order->items as $index => $item)
                            <div class="order-item-row row mb-3">
                                <div class="col-md-6">
                                    <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                        <option value="">Select Product...</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} - ${{ number_format($product->price, 2) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" placeholder="Quantity" min="1" value="{{ $item->quantity }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control item-total" placeholder="Total" value="${{ number_format($item->total, 2) }}" readonly>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger remove-item">Remove</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-item" class="btn btn-success">Add Item</button>
                        <div class="mt-3">
                            <strong>Order Total: $<span id="order-total">{{ number_format($order->total_amount, 2) }}</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="row mt-4">
                    <!-- Shipping Address -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Shipping Address</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="shipping_name">Full Name</label>
                                    <input type="text" name="shipping_address[name]" id="shipping_name" class="form-control" value="{{ $order->shipping_address['name'] }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_address">Address</label>
                                    <textarea name="shipping_address[address]" id="shipping_address" class="form-control" rows="3" required>{{ $order->shipping_address['address'] }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_city">City</label>
                                    <input type="text" name="shipping_address[city]" id="shipping_city" class="form-control" value="{{ $order->shipping_address['city'] }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_postal_code">Postal Code</label>
                                    <input type="text" name="shipping_address[postal_code]" id="shipping_postal_code" class="form-control" value="{{ $order->shipping_address['postal_code'] }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_country">Country</label>
                                    <input type="text" name="shipping_address[country]" id="shipping_country" class="form-control" value="{{ $order->shipping_address['country'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Billing Address</h5>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="same-as-shipping">
                                    <label class="form-check-label" for="same-as-shipping">Same as shipping address</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="billing_name">Full Name</label>
                                    <input type="text" name="billing_address[name]" id="billing_name" class="form-control" value="{{ $order->billing_address['name'] }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_address">Address</label>
                                    <textarea name="billing_address[address]" id="billing_address" class="form-control" rows="3" required>{{ $order->billing_address['address'] }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="billing_city">City</label>
                                    <input type="text" name="billing_address[city]" id="billing_city" class="form-control" value="{{ $order->billing_address['city'] }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_postal_code">Postal Code</label>
                                    <input type="text" name="billing_address[postal_code]" id="billing_postal_code" class="form-control" value="{{ $order->billing_address['postal_code'] }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_country">Country</label>
                                    <input type="text" name="billing_address[country]" id="billing_country" class="form-control" value="{{ $order->billing_address['country'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Order Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any special instructions or notes...">{{ $order->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card mt-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg">Update Order</button>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCounter = {{ count($order->items) }};

    // Add new item row
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('order-items');
        const newRow = createItemRow(itemCounter);
        container.appendChild(newRow);
        itemCounter++;
        updateOrderTotal();
    });

    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            if (document.querySelectorAll('.order-item-row').length > 1) {
                e.target.closest('.order-item-row').remove();
                updateOrderTotal();
            }
        }
    });

    // Update item total when product or quantity changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select') || e.target.classList.contains('quantity-input')) {
            updateItemTotal(e.target.closest('.order-item-row'));
            updateOrderTotal();
        }
    });

    // Same as shipping address checkbox
    document.getElementById('same-as-shipping').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('billing_name').value = document.getElementById('shipping_name').value;
            document.getElementById('billing_address').value = document.getElementById('shipping_address').value;
            document.getElementById('billing_city').value = document.getElementById('shipping_city').value;
            document.getElementById('billing_postal_code').value = document.getElementById('shipping_postal_code').value;
            document.getElementById('billing_country').value = document.getElementById('shipping_country').value;
        }
    });

    function createItemRow(index) {
        const row = document.createElement('div');
        row.className = 'order-item-row row mb-3';
        row.innerHTML = `
            <div class="col-md-6">
                <select name="items[${index}][product_id]" class="form-control product-select" required>
                    <option value="">Select Product...</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                        {{ $product->name }} - ${{ number_format($product->price, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${index}][quantity]" class="form-control quantity-input" placeholder="Quantity" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control item-total" placeholder="Total" readonly>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-item">Remove</button>
            </div>
        `;
        return row;
    }

    function updateItemTotal(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const totalInput = row.querySelector('.item-total');

        if (productSelect.value && quantityInput.value) {
            const price = parseFloat(productSelect.options[productSelect.selectedIndex].dataset.price);
            const quantity = parseInt(quantityInput.value);
            const total = price * quantity;
            totalInput.value = '$' + total.toFixed(2);
        } else {
            totalInput.value = '';
        }
    }

    function updateOrderTotal() {
        let total = 0;
        document.querySelectorAll('.order-item-row').forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            
            if (productSelect.value && quantityInput.value) {
                const price = parseFloat(productSelect.options[productSelect.selectedIndex].dataset.price);
                const quantity = parseInt(quantityInput.value);
                total += price * quantity;
            }
        });
        document.getElementById('order-total').textContent = total.toFixed(2);
    }

    // Initialize total calculation
    updateOrderTotal();
});
</script>
@endsection