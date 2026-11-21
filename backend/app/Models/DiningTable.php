<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningTable extends Model
{
    protected $fillable = [
        'branch_id',
        'table_number',
        'seating_capacity',
        'zone',
        'status',
        'is_active',
    ];

    protected $casts = [
        'seating_capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }

    public function sessions()
    {
        return $this->hasMany(TableSession::class);
    }

    public function activeSession()
    {
        return $this->hasOne(TableSession::class)->where('status', 'active')->latestOfMany();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
