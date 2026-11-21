<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'ingredient_id',
        'movement_type',
        'quantity_change',
        'balance_after',
        'reference_type',
        'reference_id',
        'created_by_user_id',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
