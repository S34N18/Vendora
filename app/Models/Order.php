<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'tax_amount',
        'shipping_amount',
        'status',
        'payment_status',
        'payment_method',
        'shipping_address',
        'billing_address',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
        ];

        return $badges[$this->payment_status] ?? 'bg-gray-100 text-gray-800';
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD-';
        $number = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        while (self::where('order_number', $prefix . $number)->exists()) {
            $number = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }
        
        return $prefix . $number;

{
        $lastOrder = self::latest()->first();
    $lastNumber = $lastOrder ? (int)substr($lastOrder->order_number, 3) : 0;
    return 'ORD' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

}
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function canBeShipped()
    {
        return $this->status === 'processing' && $this->payment_status === 'paid';
    }

    public function canBeDelivered()
    {
        return $this->status === 'shipped';
    }

    



    
}