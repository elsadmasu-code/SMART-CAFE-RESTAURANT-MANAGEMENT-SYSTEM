<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenStation extends Model
{
    protected $fillable = [
        'branch_id',
        'name_en',
        'name_am',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(KitchenTicket::class);
    }
}
