<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - {{ $product->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .form-control:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0,123,255,0.25); }
        textarea.form-control { height: 120px; resize: vertical; }
        .btn { padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.9; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .back-link { color: #007bff; text-decoration: none; margin-bottom: 20px; display: inline-block; }
        .error { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .current-image { margin-top: 10px; }
        .current-image img { max-width: 200px; height: auto; border-radius: 4px; border: 1px solid #ddd; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <a href="{{ route('products.show', $product->slug) }}" class="back-link">← Back to Product</a>
            <a href="{{ route('products.index') }}" class="back-link">All Products</a>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="card">
            <h1>Edit Product: {{ $product->name }}</h1>
            
            <form method="POST" action="{{ route('products.update', $product->slug) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" required>{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_price">Current Price ($) *</label>
                        <input type="number" id="current_price" name="current_price" class="form-control" 
                               step="0.01" min="0" value="{{ old('current_price', $product->current_price) }}" required>
                        @error('current_price')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="original_price">Original Price ($)</label>
                        <input type="number" id="original_price" name="original_price" class="form-control" 
                               step="0.01" min="0" value="{{ old('original_price', $product->original_price) }}">
                        @error('original_price')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" 
                               min="0" value="{{ old('quantity', $product->quantity) }}" required>
                        @error('quantity')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" class="form-control" 
                               step="0.01" min="0" value="{{ old('weight', $product->weight) }}">
                        @error('weight')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="sku">SKU</label>
                        <input type="text" id="sku" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    @if($product->image)
                        <div class="current-image">
                            <p><strong>Current Image:</strong></p>
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            <p><small>Upload a new image to replace the current one</small></p>
                        </div>
                    @endif
                    @error('image')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1" 
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label for="is_active">Active</label>
                        </div>
                        @error('is_active')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label for="is_featured">Featured</label>
                        </div>
                        @error('is_featured')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="{{ route('products.show', $product->slug) }}" class="btn btn-secondary">Cancel</a>
                    
                    @can('delete', $product)
                        <form method="POST" action="{{ route('products.destroy', $product->slug) }}" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Product</button>
                        </form>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</body>
</html>