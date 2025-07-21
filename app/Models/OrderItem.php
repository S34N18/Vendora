<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

  
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with the order item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total for this item
     */
    public function calculateTotal()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = $item->price * $item->quantity;
        });
    }
}




