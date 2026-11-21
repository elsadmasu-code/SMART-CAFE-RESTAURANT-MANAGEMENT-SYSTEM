<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'recipe_name',
        'yield_quantity',
        'preparation_notes',
    ];

    protected $casts = [
        'yield_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
