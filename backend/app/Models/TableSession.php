<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    protected $fillable = [
        'session_uuid',
        'dining_table_id',
        'customer_profile_id',
        'assigned_waiter_id',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'assigned_waiter_id');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerProfile::class, 'customer_profile_id');
    }
}
