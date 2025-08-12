@extends('layouts.app')

@section('title', 'Products - Mark\'s Online Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endpush

@section('content')
<div class="products-container">
    <div class="products-wrapper">
        <div class="products-header">
            <h1>Products</h1>
            <div class="auth-section">
                @auth
                    <span class="user-info">Welcome, {{ Auth::user()->name }}!</span>
                    {{-- Only show Add New Product for admin users --}}
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.products.create') }}" class="btn btn-success">Add New Product</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
                @endauth
            </div>
        </div>

        <div id="alert-container"></div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}" class="search-box">
                <button type="submit" class="btn btn-primary">Search</button>
                @if(request('search'))
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <!-- Category Filter -->
        <div class="category-filter">
            <a href="{{ route('products.index') }}" class="filter-btn {{ !request('category') ? 'active' : '' }}">All Categories</a>
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                   class="filter-btn {{ request('category') == $category->slug ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            @forelse($products as $product)
                <div class="product-card" data-product-id="{{ $product->id }}">
                    @if($product->is_on_sale)
                        <div class="sale-badge">Sale</div>
                    @endif

                    <h3 class="product-title">{{ $product->name }}</h3>
                    <p class="product-description">{{ Str::limit($product->description, 120) }}</p>

                    <div class="product-price">
                        ${{ number_format($product->current_price, 2) }}
                        @if($product->is_on_sale)
                            <span class="original-price">
                                ${{ number_format($product->original_price, 2) }}
                            </span>
                        @endif
                    </div>

                    <div class="product-meta">
                        <p><strong>Stock:</strong> 
                            <span class="stock-status {{ $product->quantity > 0 ? 'stock-in' : 'stock-out' }}">
                                {{ $product->stock_status_text }}
                            </span>
                        </p>
                        <p><strong>Category:</strong> {{ $product->category->name }}</p>
                        <p><strong>Available:</strong> {{ $product->quantity }} units</p>
                    </div>

                    {{-- Different sections based on user type --}}
                    @guest
                        {{-- Guest users: Show login prompt for cart functionality --}}
                        @if($product->quantity > 0)
                            <div class="guest-cart-section">
                                <div class="login-prompt">
                                    <p class="login-message">
                                        <a href="{{ route('login') }}" class="login-link">Login</a> to add items to your cart
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="out-of-stock-message">
                                <p>Currently out of stock</p>
                            </div>
                        @endif
                    @endguest

                    @auth
                        @if(Auth::user()->isAdmin())
                            {{-- Admin users: Show admin controls --}}
                            <div class="admin-controls">
                                <div class="admin-badge">Admin View</div>
                                <div class="admin-actions">
                                    <a href="{{ route('admin.products.edit', $product->slug) }}" class="btn btn-warning btn-sm">Edit Product</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->slug) }}" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- Regular users: Show cart functionality --}}
                            @if($product->quantity > 0)
                                <div class="cart-section">
                                    <div class="quantity-selector">
                                        <span class="quantity-label">Quantity:</span>
                                        <div class="quantity-controls">
                                            <button type="button" class="quantity-btn" onclick="changeQuantity({{ $product->id }}, -1)">-</button>
                                            <input type="number" id="quantity-{{ $product->id }}" class="quantity-input" value="1" min="1" max="{{ min($product->quantity, 10) }}">
                                            <button type="button" class="quantity-btn" onclick="changeQuantity({{ $product->id }}, 1)">+</button>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-cart" onclick="addToCart({{ $product->id }})" id="cart-btn-{{ $product->id }}">
                                        <span class="btn-text">Add to Cart</span>
                                        <span class="btn-loading" style="display: none;">Adding...</span>
                                    </button>
                                </div>
                            @else
                                <div class="out-of-stock-message">
                                    <p>Currently out of stock</p>
                                </div>
                            @endif
                        @endif
                    @endauth

                    <div class="product-actions">
                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h3>No Products Found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrapper">
            <div class="pagination">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Cart floating button - Only show for regular users --}}
@auth
    @if(!Auth::user()->isAdmin())
        <a href="{{ route('cart.index') }}" class="cart-float-btn" id="cartFloatBtn">
            🛒
            <span class="cart-float-badge" id="cartFloatBadge">{{ session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}</span>
        </a>
    @endif
@endauth
@endsection

@push('scripts')
<script>
    // Cart functionality - Only for regular users
    @auth
        @if(!Auth::user()->isAdmin())
            // Change quantity in the product card
            function changeQuantity(productId, change) {
                const quantityInput = document.getElementById(`quantity-${productId}`);
                const currentQuantity = parseInt(quantityInput.value);
                const newQuantity = currentQuantity + change;
                const maxQuantity = parseInt(quantityInput.max);
                
                if (newQuantity >= 1 && newQuantity <= maxQuantity) {
                    quantityInput.value = newQuantity;
                }
            }

            // Add product to cart
            function addToCart(productId) {
                const quantityInput = document.getElementById(`quantity-${productId}`);
                const quantity = parseInt(quantityInput.value);
                const cartBtn = document.getElementById(`cart-btn-${productId}`);
                const btnText = cartBtn.querySelector('.btn-text');
                const btnLoading = cartBtn.querySelector('.btn-loading');
                const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                
                // Show loading state
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                cartBtn.disabled = true;
                productCard.classList.add('loading');
                
                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        updateCartCount(data.cart_count);
                        
                        // Show success message
                        showMessage(data.message, 'success');
                        
                        // Reset quantity to 1
                        quantityInput.value = 1;
                        
                        // Add visual feedback
                        cartBtn.classList.add('success');
                        btnText.textContent = 'Added!';
                        
                        setTimeout(() => {
                            cartBtn.classList.remove('success');
                            btnText.textContent = 'Add to Cart';
                        }, 2000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Something went wrong. Please try again.', 'error');
                })
                .finally(() => {
                    // Reset loading state
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    cartBtn.disabled = false;
                    productCard.classList.remove('loading');
                });
            }

            // Update cart count in floating button
            function updateCartCount(count) {
                const cartBadge = document.getElementById('cartFloatBadge');
                const headerCartCount = document.getElementById('cart-count');
                const mobileCartCount = document.getElementById('mobile-cart-count');
                
                if (cartBadge) {
                    cartBadge.textContent = count;
                    cartBadge.style.display = count > 0 ? 'block' : 'none';
                }
                
                if (headerCartCount) {
                    headerCartCount.textContent = count;
                }
                
                if (mobileCartCount) {
                    mobileCartCount.textContent = count;
                }
            }

            // Initialize cart count on page load
            document.addEventListener('DOMContentLoaded', function() {
                fetch('/cart/count')
                    .then(response => response.json())
                    .then(data => {
                        updateCartCount(data.count);
                    })
                    .catch(error => console.error('Error fetching cart count:', error));
            });

            // Handle quantity input changes
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    const productId = this.id.replace('quantity-', '');
                    const min = parseInt(this.min);
                    const max = parseInt(this.max);
                    let value = parseInt(this.value);
                    
                    if (value < min) {
                        this.value = min;
                    } else if (value > max) {
                        this.value = max;
                    }
                });
            });
        @endif
    @endauth

    // Show alert messages (available for all users)
    function showMessage(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <span>${message}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script>
@endpush