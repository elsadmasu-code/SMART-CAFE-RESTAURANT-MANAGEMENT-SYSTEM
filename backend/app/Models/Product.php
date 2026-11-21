<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'branch_id',
        'category_id',
        'sku',
        'price',
        'cost_price',
        'preparation_time_minutes',
        'image_url',
        'is_available',
        'is_featured',
        'is_fasting',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'preparation_time_minutes' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_fasting' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function addons()
    {
        return $this->hasMany(ProductAddon::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function getLocalizedName(string $locale = 'en'): string
    {
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');

        return $translation ? $translation->name : "Product #{$this->id}";
    }

    public function getLocalizedDescription(string $locale = 'en'): ?string
    {
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');

        return $translation ? $translation->description : null;
    }
}
