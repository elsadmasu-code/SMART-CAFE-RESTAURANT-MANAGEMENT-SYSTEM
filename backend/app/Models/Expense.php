<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'branch_id',
        'expense_category_id',
        'amount',
        'currency',
        'description',
        'expense_date',
        'payment_method',
        'receipt_voucher_number',
        'receipt_attachment_url',
        'paid_by_user_id',
        'approved_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
