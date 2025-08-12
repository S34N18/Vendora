@extends('layouts.app')

@section('title', 'Shopping Cart - Vendora Supermarket')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endpush

@section('content')
<div class="cart-container">
    <div class="cart-wrapper">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Continue Shopping</a>
        </div>

        <div id="cart-messages"></div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if(count($cartItems) > 0)
            <div class="cart-content">
                <div class="cart-items">
                    @foreach($cartItems as $item)
                        <div class="cart-item" data-product-id="{{ $item['product']->id }}">
                            <div class="item-info">
                                <h3>{{ $item['product']->name }}</h3>
                                <p class="item-description">{{ Str::limit($item['product']->description, 80) }}</p>
                                <div class="item-price">${{ number_format($item['product']->current_price, 2) }} each</div>
                                <div class="item-subtotal">
                                    Subtotal: $<span class="subtotal-amount">{{ number_format($item['subtotal'], 2) }}</span>
                                </div>
                            </div>
                            
                            <div class="item-controls">
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] - 1 }})" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                                    <input type="number" class="quantity-input" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->quantity }}" onchange="updateQuantity({{ $item['product']->id }}, this.value)">
                                    <button class="quantity-btn" onclick="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] + 1 }})" {{ $item['quantity'] >= $item['product']->quantity ? 'disabled' : '' }}>+</button>
                                </div>
                                
                                <button class="remove-btn" onclick="removeFromCart({{ $item['product']->id }})">
                                    Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Items ({{ array_sum(array_column($cartItems, 'quantity')) }})</span>
                            <span id="items-total">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span class="shipping-free">Free</span>
                        </div>
                        <div class="summary-row total-row">
                            <span><strong>Total</strong></span>
                            <span id="cart-total"><strong>${{ number_format($total, 2) }}</strong></span>
                        </div>
                    </div>
                    
                    <div class="checkout-actions">
                        <button class="btn btn-primary btn-large" onclick="proceedToCheckout()">
                            Proceed to Checkout
                        </button>
                        <button class="btn btn-secondary" onclick="clearCart()">
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update quantity in cart
    function updateQuantity(productId, newQuantity) {
        newQuantity = parseInt(newQuantity);
        
        if (newQuantity < 1) {
            showMessage('Quantity must be at least 1', 'error');
            return;
        }
        
        const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
        cartItem.classList.add('updating');
        
        fetch('/cart/update/' + productId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                quantity: newQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the quantity input
                const quantityInput = cartItem.querySelector('.quantity-input');
                quantityInput.value = newQuantity;
                
                // Update subtotal
                const subtotalElement = cartItem.querySelector('.subtotal-amount');
                subtotalElement.textContent = data.item_subtotal;
                
                // Update cart totals
                document.getElementById('items-total').textContent = '$' + data.cart_total;
                document.getElementById('cart-total').innerHTML = '<strong>$' + data.cart_total + '</strong>';
                
                // Update cart count in header
                updateCartCount(data.cart_count);
                
                // Update quantity controls
                updateQuantityControls(productId, newQuantity, data.max_quantity);
                
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
                // Reset quantity input to original value
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Something went wrong. Please try again.', 'error');
        })
        .finally(() => {
            cartItem.classList.remove('updating');
        });
    }

    // Remove item from cart
    function removeFromCart(productId) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }
        
        const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
        cartItem.classList.add('removing');
        
        fetch('/cart/remove/' + productId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from DOM
                cartItem.remove();
                
                // Update cart totals
                if (data.cart_count > 0) {
                    document.getElementById('items-total').textContent = '$' + data.cart_total;
                    document.getElementById('cart-total').innerHTML = '<strong>$' + data.cart_total + '</strong>';
                } else {
                    // Reload page to show empty cart
                    location.reload();
                }
                
                // Update cart count in header
                updateCartCount(data.cart_count);
                
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Something went wrong. Please try again.', 'error');
        })
        .finally(() => {
            cartItem.classList.remove('removing');
        });
    }

    // Clear entire cart
    function clearCart() {
        if (!confirm('Are you sure you want to clear your entire cart?')) {
            return;
        }
        
        fetch('/cart/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Something went wrong. Please try again.', 'error');
        });
    }

    // Proceed to checkout - FIXED VERSION
    function proceedToCheckout() {
        // Check if cart has items
        const cartItems = document.querySelectorAll('.cart-item');
        if (cartItems.length === 0) {
            showMessage('Your cart is empty. Add some items first!', 'error');
            return;
        }
        
        // Show loading state
        const checkoutBtn = document.querySelector('.btn-primary.btn-large');
        const originalText = checkoutBtn.textContent;
        checkoutBtn.textContent = 'Processing...';
        checkoutBtn.disabled = true;
        
        // Redirect to checkout page
        try {
            window.location.href = "{{ route('checkout.index') }}";
        } catch (error) {
            // Fallback if route helper doesn't work in JS
            window.location.href = '/checkout';
        }
        
        // Reset button state after a delay (in case redirect fails)
        setTimeout(() => {
            checkoutBtn.textContent = originalText;
            checkoutBtn.disabled = false;
        }, 3000);
    }

    // Update quantity controls (enable/disable buttons)
    function updateQuantityControls(productId, currentQuantity, maxQuantity) {
        const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
        const decreaseBtn = cartItem.querySelector('.quantity-btn:first-child');
        const increaseBtn = cartItem.querySelector('.quantity-btn:last-child');
        
        decreaseBtn.disabled = currentQuantity <= 1;
        increaseBtn.disabled = currentQuantity >= maxQuantity;
    }

    // Update cart count in header
    function updateCartCount(count) {
        const headerCartCount = document.getElementById('cart-count');
        const mobileCartCount = document.getElementById('mobile-cart-count');
        
        if (headerCartCount) {
            headerCartCount.textContent = count;
        }
        
        if (mobileCartCount) {
            mobileCartCount.textContent = count;
        }
    }

    // Show alert messages
    function showMessage(message, type) {
        const messagesContainer = document.getElementById('cart-messages');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <span>${message}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        messagesContainer.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Handle quantity input changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.closest('[data-product-id]').dataset.productId;
            const min = parseInt(this.min);
            const max = parseInt(this.max);
            let value = parseInt(this.value);
            
            if (value < min) {
                this.value = min;
                value = min;
            } else if (value > max) {
                this.value = max;
                value = max;
            }
            
            updateQuantity(productId, value);
        });
    });
</script>
@endpush