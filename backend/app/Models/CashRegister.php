<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'branch_id',
        'cashier_user_id',
        'opening_balance',
        'cash_received',
        'cash_paid_out',
        'expected_closing_balance',
        'actual_closing_balance',
        'discrepancy',
        'status',
        'opened_at',
        'closed_at',
        'closing_notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_paid_out' => 'decimal:2',
        'expected_closing_balance' => 'decimal:2',
        'actual_closing_balance' => 'decimal:2',
        'discrepancy' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }
}
