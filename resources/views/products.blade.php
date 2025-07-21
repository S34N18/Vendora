
<!-- resources/views/products.blade.php -->
<!-- This file displays a list of products with search and filter options -->
<!-- It includes a header, search form, category filters, product cards, and pagination -->
@extends('layouts.app')


<!--adds css file for product styles-->
@push('styles')
<!-- <link rel="stylesheet" href="{{ asset('css/products.css') }}"> -->
@endpush
<!-- Sets the title of the page -->
@section('content')
<div class="products-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Our Products</h1>
        <p class="page-subtitle">Discover our amazing collection of products</p>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <!-- Search Form with GET  to filter products -->
        <form method="GET" action="{{ route('products.index') }}" class="filters-form">
            <div class="filters-row">
                <!-- Search Input -->
                <div class="search-input-wrapper">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search products..." 
                           class="search-input">
                </div>
                
                <!-- Category Filter -->
                <div class="filter-select-wrapper">
                    <select name="category" class="filter-select">
                        <option value="all" {{ request('category') == 'all' ? 'selected' : '' }}>All Categories</option>

                        <!-- Loop through categories and create options -->
                        <!-- If no category is selected, show all categories -->
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->emoji }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Sort Filter -->
                <div class="filter-select-wrapper">
                    <select name="sort" class="filter-select">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured</option>
                    </select>
                </div>
                
                <!-- Search Button -->
                <button type="submit" class="search-btn">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Category Quick Filters -->
    <div class="category-filters">
        <a href="{{ route('products.index') }}" 
           class="category-filter {{ !request('category') || request('category') == 'all' ? 'active' : '' }}">
            All Products
        </a>
        @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
               class="category-filter {{ request('category') == $category->slug ? 'active' : '' }}">
                {{ $category->emoji }} {{ $category->name }}
            </a>
        @endforeach
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <p class="results-text">
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
            @if(request('search'))
                for "<strong>{{ request('search') }}</strong>"
            @endif
        </p>
        
        @if(request()->hasAny(['search', 'category', 'sort']))
            <a href="{{ route('products.index') }}" class="clear-filters">
                Clear all filters
            </a>
        @endif
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="products-grid">
            @foreach($products as $product)
                <div class="product-card">
                    <!-- Product Image -->
                    <div class="product-image-wrapper">
                        <a href="{{ route('products.show', $product->slug) }}">
                            <div class="product-image">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}"
                                         class="product-img">
                                @else
                                    <div class="product-placeholder">
                                        <svg class="placeholder-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        
                        <!-- Badges -->
                        <div class="product-badges">
                            @if($product->is_on_sale)
                                <span class="badge sale-badge">
                                    -{{ $product->discount_percentage }}%
                                </span>
                            @endif
                            @if($product->is_featured)
                                <span class="badge featured-badge">
                                    Featured
                                </span>
                            @endif
                            @if($product->is_new)
                                <span class="badge new-badge">
                                    New
                                </span>
                            @endif
                        </div>

                        <!-- Stock Badge -->
                        <div class="stock-badge-wrapper">
                            <span class="stock-badge stock-{{ $product->stock_status_class }}">
                                {{ $product->stock_status_text }}
                            </span>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info">
                        <!-- Category -->
                        <div class="product-category">
                            {{ $product->category->emoji }} {{ $product->category->name }}
                        </div>
                        
                        <!-- Product Name -->
                        <h3 class="product-name">
                            <a href="{{ route('products.show', $product->slug) }}">
                                {{ $product->name }}
                            </a>
                        </h3>
                        
                        <!-- Rating -->
                        @if($product->reviews_count > 0)
                            <div class="product-rating">
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="star {{ $i <= $product->average_rating ? 'star-filled' : 'star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="rating-count">({{ $product->reviews_count }})</span>
                            </div>
                        @endif
                        
                        <!-- Price -->
                        <div class="product-price">
                            <span class="current-price">
                                ${{ number_format($product->current_price, 2) }}
                            </span>
                            @if($product->is_on_sale)
                                <span class="original-price">
                                    ${{ number_format($product->original_price, 2) }}
                                </span>
                            @endif
                        </div>
                        
                        <!-- Add to Cart Button -->
                        @if($product->quantity > 0)
                            <button onclick="addToCart({{ $product->id }})" class="add-to-cart-btn">
                                Add to Cart
                            </button>
                        @else
                            <button class="add-to-cart-btn disabled" disabled>
                                Out of Stock
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @else
        <!-- No Products Found -->
        <div class="no-products">
            <div class="no-products-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4.5"/>
                </svg>
            </div>
            <h3 class="no-products-title">No products found</h3>
            <p class="no-products-text">
                @if(request()->hasAny(['search', 'category']))
                    Try adjusting your search criteria or browse all products.
                @else
                    Check back later for new products.
                @endif
            </p>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('products.index') }}" class="view-all-link">
                    View all products
                </a>
            @endif
        </div>
    @endif
</div>

<script>
function addToCart(productId) {
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Product added to cart!', 'success');
            // Update cart count if you have one
            updateCartCount(data.cart_count);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong!', 'error');
    });
}

function showNotification(message, type) {
    // Create a simple notification
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function updateCartCount(count) {
    const cartCountElement = document.querySelector('#cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}
</script>
@endsection