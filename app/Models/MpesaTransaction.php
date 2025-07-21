<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'phone',
        'amount',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt_number',
        'transaction_date',
        'status',
        'result_desc'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
