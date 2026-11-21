<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'code',
        'name',
        'city',
        'region',
        'country',
        'address',
        'phone',
        'tin_number',
        'currency_code',
        'default_vat_rate',
        'default_service_charge',
        'is_active',
    ];

    protected $casts = [
        'default_vat_rate' => 'decimal:2',
        'default_service_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tables()
    {
        return $this->hasMany(DiningTable::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
