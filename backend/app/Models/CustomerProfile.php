<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'preferred_language',
        'loyalty_points',
        'total_spend',
    ];

    protected $casts = [
        'total_spend' => 'decimal:2',
        'loyalty_points' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function preferences()
    {
        return $this->hasOne(CustomerPreference::class);
    }
}
