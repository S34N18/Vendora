<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-link {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .auth-info {
            color: #fff;
            font-weight: 500;
        }

        /* Alert Styles */
        .alert {
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #fff;
            border-color: rgba(40, 167, 69, 0.3);
        }

        /* Admin Actions */
        .admin-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .admin-actions h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .admin-actions p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Product Detail Grid */
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .product-image {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: translateY(-5px);
        }

        .product-image img {
            width: 100%;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .product-image img:hover {
            transform: scale(1.05);
        }

        .product-info {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .product-info h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
            color: #e74c3c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .original-price {
            font-size: 1.2rem;
            text-decoration: line-through;
            color: #999;
            font-weight: 500;
        }

        .sale-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .description {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .product-meta {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
        }

        .product-meta p {
            margin-bottom: 12px;
            font-size: 1rem;
        }

        .product-meta strong {
            color: #333;
            font-weight: 600;
        }

        .badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-new {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .badge-featured {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #333;
        }

        .badge-sale {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .rating-section {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-top: 20px;
        }

        /* Button Styles */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-right: 15px;
            display: inline-block;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #333;
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(255, 193, 7, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(220, 53, 69, 0.4);
        }

        /* Related Products */
        .related-products {
            margin-top: 60px;
        }

        .related-products h3 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 30px;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .related-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .related-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .related-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .related-card h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .related-card .price {
            font-size: 1.4rem;
            font-weight: 800;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .related-card p {
            color: #666;
            margin-bottom: 15px;
        }

        /* Stock Status */
        .stock-in {
            color: #28a745;
            font-weight: 700;
        }

        .stock-out {
            color: #dc3545;
            font-weight: 700;
        }

        /* No Image Placeholder */
        .no-image-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: #666;
            text-align: center;
        }

        .no-image-placeholder div {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .header-section {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .product-info h1 {
                font-size: 2rem;
            }
            
            .price {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .product-info,
            .product-image {
                padding: 25px;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <a href="{{ route('products.index') }}" class="back-link">← Back to Products</a>
            <div class="auth-info">
                @auth
                    <span>Logged in as: {{ Auth::user()->name }}</span>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login to manage products</a>
                @endauth
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @auth
            @canany(['update', 'delete'], $product)
                <div class="admin-actions">
                    <h3>Product Management</h3>
                    <p>You have permission to manage this product.</p>
                    
                    @can('update', $product)
                        <a href="{{ route('products.edit', $product->slug) }}" class="btn btn-warning">Edit Product</a>
                    @endcan
                    
                    @can('delete', $product)
                        <form method="POST" action="{{ route('products.destroy', $product->slug) }}" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Product</button>
                        </form>
                    @endcan
                    
                    <a href="{{ route('products.create') }}" class="btn btn-success">Add New Product</a>
                </div>
            @endcanany
        @endauth
        
        <div class="product-detail">
            <div class="product-image">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                @else
                    <div class="no-image-placeholder">
                        <div>
                            <div>📦</div>
                            <div>No Image Available</div>
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="product-info">
                <h1>{{ $product->name }}</h1>
                <div class="price">
                    ${{ number_format($product->current_price, 2) }}
                    @if($product->is_on_sale)
                        <span class="original-price">${{ number_format($product->original_price, 2) }}</span>
                        <span class="sale-badge">
                            {{ number_format((($product->original_price - $product->current_price) / $product->original_price) * 100) }}% OFF
                        </span>
                    @endif
                </div>
                
                <div class="description">
                    {{ $product->description }}
                </div>
                
                <div class="product-meta">
                    <p><strong>Category:</strong> {{ $product->category->name }}</p>
                    @if($product->sku)
                        <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    @endif
                    <p><strong>Stock Status:</strong> 
                        <span class="{{ $product->quantity > 0 ? 'stock-in' : 'stock-out' }}">
                            {{ $product->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </p>
                    <p><strong>Quantity Available:</strong> {{ $product->quantity }}</p>
                    @if($product->weight)
                        <p><strong>Weight:</strong> {{ $product->weight }} kg</p>
                    @endif
                    
                    <div class="badges">
                        @if($product->created_at->diffInDays() <= 30)
                            <span class="badge badge-new">NEW</span>
                        @endif
                        @if($product->is_featured)
                            <span class="badge badge-featured">FEATURED</span>
                        @endif
                        @if($product->is_on_sale)
                            <span class="badge badge-sale">ON SALE</span>
                        @endif
                    </div>
                </div>
                
                <div class="rating-section">
                    @if($product->approvedReviews && $product->approvedReviews->count() > 0)
                        <p><strong>Customer Rating:</strong> 
                            ⭐ {{ number_format($product->approvedReviews->avg('rating'), 1) }}/5.0 
                            ({{ $product->approvedReviews->count() }} {{ Str::plural('review', $product->approvedReviews->count()) }})
                        </p>
                    @else
                        <p><em>No reviews yet - Be the first to review!</em></p>
                    @endif
                </div>
            </div>
        </div>
        
        @if($relatedProducts && $relatedProducts->count() > 0)
            <div class="related-products">
                <h3>Related Products</h3>
                <div class="related-grid">
                    @foreach($relatedProducts as $related)
                        <div class="related-card">
                            @if($related->image)
                                <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}">
                            @endif
                            <h4>{{ $related->name }}</h4>
                            <p class="price">
                                ${{ number_format($related->current_price, 2) }}
                            </p>
                            <p>{{ Str::limit($related->description, 80) }}</p>
                            <a href="{{ route('products.show', $related->slug) }}" class="btn btn-primary">View Details</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>
</html>