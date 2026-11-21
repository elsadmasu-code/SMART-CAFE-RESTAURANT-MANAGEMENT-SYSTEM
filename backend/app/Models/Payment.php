<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_uuid',
        'order_id',
        'branch_id',
        'payment_method',
        'amount',
        'currency',
        'transaction_reference',
        'status',
        'proof_image_url',
        'verified_by_user_id',
        'verified_at',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
