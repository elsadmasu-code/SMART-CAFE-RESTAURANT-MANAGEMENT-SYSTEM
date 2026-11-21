<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'branch_id',
        'name_en',
        'name_am',
        'code',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
