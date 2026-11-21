<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'receipt_number',
        'issued_by_user_id',
        'total_amount',
        'payment_method_snapshot',
        'printed_count',
        'created_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'printed_count' => 'integer',
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }
}
