<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddon extends Model
{
    protected $fillable = [
        'product_id',
        'addon_name_en',
        'addon_name_am',
        'extra_price',
        'is_active',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
