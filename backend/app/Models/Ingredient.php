<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'branch_id',
        'inventory_location_id',
        'name_en',
        'name_am',
        'sku',
        'unit_of_measure',
        'current_stock',
        'minimum_stock_level',
        'reorder_level',
        'cost_per_unit',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:3',
        'minimum_stock_level' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock_level;
    }
}
