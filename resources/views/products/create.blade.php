<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
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
        .btn:hover { opacity: 0.9; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .back-link { color: #007bff; text-decoration: none; margin-bottom: 20px; display: inline-block; }
        .error { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
    </style>
</head>
<body>
    <div class="container">
        {{-- FIXED: Use admin.products.index instead of products.index --}}
        <a href="{{ route('admin.products.index') }}" class="back-link">← Back to Products</a>
        
        <div class="card">
            <h1>Create New Product</h1>
            
            {{-- FIXED: Use admin.products.store instead of products.store --}}
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_price">Current Price ($) *</label>
                        <input type="number" id="current_price" name="current_price" class="form-control" 
                               step="0.01" min="0" value="{{ old('current_price') }}" required>
                        @error('current_price')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="original_price">Original Price ($)</label>
                        <input type="number" id="original_price" name="original_price" class="form-control" 
                               step="0.01" min="0" value="{{ old('original_price') }}">
                        @error('original_price')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" 
                               min="0" value="{{ old('quantity', 0) }}" required>
                        @error('quantity')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" class="form-control" 
                               step="0.01" min="0" value="{{ old('weight') }}">
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
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                        <input type="text" id="sku" name="sku" class="form-control" value="{{ old('sku') }}">
                        @error('sku')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    @error('image')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label for="is_active">Active</label>
                        </div>
                        @error('is_active')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label for="is_featured">Featured</label>
                        </div>
                        @error('is_featured')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Product</button>
                    {{-- FIXED: Use admin.products.index instead of products.index --}}
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>