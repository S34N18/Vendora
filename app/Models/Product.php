<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'current_price',
        'original_price',
        'quantity',
        'sku',
        'image',
        'images',
        'is_active',
        'is_featured',
        'featured_until',
        'category_id',
        'weight',
        'attributes',
        'sort_order',
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'images' => 'array',
        'attributes' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Relationships
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                    ->where(function ($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('current_price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('current_price', '<=', $max);
        }
        return $query;
    }

    /**
     * Accessors
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->original_price && $this->original_price > $this->current_price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->is_on_sale) {
            return 0;
        }
        return round((($this->original_price - $this->current_price) / $this->original_price) * 100);
    }

    public function getStockStatusTextAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'Out of Stock';
        } elseif ($this->quantity <= 5) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    public function getStockStatusClassAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'text-danger';
        } elseif ($this->quantity <= 5) {
            return 'text-warning';
        }
        return 'text-success';
    }

    public function getIsNewAttribute(): bool
    {
        return $this->created_at && $this->created_at->diffInDays() <= 30;
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->current_price, 2);
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        return $this->original_price ? '$' . number_format($this->original_price, 2) : '';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Methods
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    public function canBackorder(): bool
    {
        return $this->quantity <= 0 && $this->is_active;
    }

    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->quantity >= $quantity) {
            $this->decrement('quantity', $quantity);
            return true;
        }
        return false;
    }

    public function incrementStock(int $quantity = 1): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Get similar products based on category
     */
    public function getSimilarProducts(int $limit = 4)
    {
        return self::where('category_id', $this->category_id)
                   ->where('id', '!=', $this->id)
                   ->active()
                   ->inStock()
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }
}