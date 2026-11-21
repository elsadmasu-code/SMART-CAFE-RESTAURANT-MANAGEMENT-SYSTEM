<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WastageRecord extends Model
{
    protected $fillable = [
        'branch_id',
        'ingredient_id',
        'wasted_quantity',
        'estimated_cost_loss',
        'reason_code',
        'reported_by_user_id',
        'approved_by_user_id',
    ];

    protected $casts = [
        'wasted_quantity' => 'decimal:3',
        'estimated_cost_loss' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }
}
