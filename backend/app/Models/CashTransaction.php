<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cash_register_id',
        'transaction_type',
        'amount',
        'reason',
        'authorized_by_user_id',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function register()
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }
}
