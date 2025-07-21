<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Apply authentication middleware to specific methods
     */
    public function __construct()
    {
        // Only authenticated admin users can create, edit, update, delete products
        $this->middleware('admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of products with filtering and search
     */
    public function index(Request $request): View
    {
        // Get all active categories for the filter buttons
        $categories = Category::active()->orderBy('name')->get();
        
        // Build the products query
        $query = Product::with(['category', 'approvedReviews'])
                       ->active()
                       ->orderBy('sort_order')
                       ->orderBy('created_at', 'desc');
        
        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        // Apply category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('current_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('current_price', 'desc');
                break;
            case 'rating':
                $query->withAvg('approvedReviews', 'rating')
                      ->orderBy('approved_reviews_avg_rating', 'desc');
                break;
            case 'popular':
                $query->withCount('approvedReviews')
                      ->orderBy('approved_reviews_count', 'desc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc');
                break;
            default: // latest
                $query->orderBy('created_at', 'desc');
        }
        
        // Paginate results
        $products = $query->paginate(12);
        
        return view('products.index', compact('products', 'categories'));
    }
    
    /**
     * Display the specified product
     */
    public function show(Product $product): View
    {
        // Load relationships
        $product->load([
            'category',
            'approvedReviews.user',
            'approvedReviews' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);
        
        // Get related products
        $relatedProducts = Product::where('category_id', $product->category_id)
                                 ->where('id', '!=', $product->id)
                                 ->active()
                                 ->inStock()
                                 ->limit(4)
                                 ->get();
        
        return view('products.show', compact('product', 'relatedProducts'));
    }
    
    /**
     * Show the form for creating a new product
     */
    public function create(): View
    {
        // Remove this line since we're using middleware instead
        // $this->authorize('create', Product::class);
        
        $categories = Category::active()->orderBy('name')->get();
        
        return view('products.create', compact('categories'));
    }
    
    /**
     * Store a newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        // Remove this line since we're using middleware instead
        // $this->authorize('create', Product::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'current_price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|unique:products,sku',
            'image' => 'nullable|image|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
        ]);
        
        // Auto-generate slug from name
        $validated['slug'] = \Str::slug($validated['name']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        
        $product = Product::create($validated);
        
        return redirect()->route('products.show', $product)
                        ->with('success', 'Product created successfully!');
    }
    
    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product): View
    {
        // Remove this line since we're using middleware instead
        // $this->authorize('update', $product);
        
        $categories = Category::active()->orderBy('name')->get();
        
        return view('products.edit', compact('product', 'categories'));
    }
    
    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        // Remove this line since we're using middleware instead
        // $this->authorize('update', $product);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'current_price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'image' => 'nullable|image|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);
        
        // Update slug if name changed
        if ($validated['name'] !== $product->name) {
            $validated['slug'] = \Str::slug($validated['name']);
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        
        $product->update($validated);
        
        return redirect()->route('products.show', $product)
                        ->with('success', 'Product updated successfully!');
    }
    
    /**
     * Remove the specified product
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Remove this line since we're using middleware instead
        // $this->authorize('delete', $product);
        
        // Delete associated image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        
        return redirect()->route('products.index')
                        ->with('success', 'Product deleted successfully!');
    }
    
    /**
     * API endpoint for AJAX product search
     */
    public function search(Request $request)
    {
        $query = Product::with('category')
                       ->active()
                       ->limit(10);
        
        if ($request->filled('q')) {
            $query->search($request->q);
        }
        
        $products = $query->get(['id', 'name', 'slug', 'current_price', 'image', 'category_id']);
        
        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => number_format($product->current_price, 2),
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'category' => $product->category->name,
                    'url' => route('products.show', $product->slug)
                ];
            })
        ]);
    }

    /**
 * Display a listing of products for admin (with inactive products)
 */
public function adminIndex(Request $request): View
{
    // Get all categories (including inactive ones for admin)
    $categories = Category::orderBy('name')->get();
    
    // Build the products query - show ALL products for admin (active and inactive)
    $query = Product::with(['category', 'approvedReviews'])
                   ->orderBy('sort_order')
                   ->orderBy('created_at', 'desc');
    
    // Apply search filter
    if ($request->filled('search')) {
        $query->search($request->search);
    }
    
    // Apply category filter
    if ($request->filled('category') && $request->category !== 'all') {
        $query->byCategory($request->category);
    }
    
    // Apply status filter for admin
    if ($request->filled('status')) {
        if ($request->status === 'active') {
            $query->active();
        } elseif ($request->status === 'inactive') {
            $query->inactive();
        }
        // If 'all', don't add any status filter
    }
    
    // Apply sorting
    $sortBy = $request->get('sort', 'latest');
    switch ($sortBy) {
        case 'price_low':
            $query->orderBy('current_price', 'asc');
            break;
        case 'price_high':
            $query->orderBy('current_price', 'desc');
            break;
        case 'rating':
            $query->withAvg('approvedReviews', 'rating')
                  ->orderBy('approved_reviews_avg_rating', 'desc');
            break;
        case 'popular':
            $query->withCount('approvedReviews')
                  ->orderBy('approved_reviews_count', 'desc');
            break;
        case 'featured':
            $query->orderBy('is_featured', 'desc');
            break;
        case 'name':
            $query->orderBy('name', 'asc');
            break;
        default: // latest
            $query->orderBy('created_at', 'desc');
    }
    
    // Paginate results
    $products = $query->paginate(15); // Show more products per page for admin
    
    return view('admin.products.index', compact('products', 'categories'));
}







}