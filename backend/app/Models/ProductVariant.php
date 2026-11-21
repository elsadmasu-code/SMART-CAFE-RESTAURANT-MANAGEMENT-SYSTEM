<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'variant_name_en',
        'variant_name_am',
        'price_modifier',
        'is_default',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
