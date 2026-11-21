<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCall extends Model
{
    protected $fillable = [
        'branch_id',
        'table_id',
        'request_type',
        'notes',
        'status',
        'handled_by_waiter_id',
        'acknowledged_at',
        'completed_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'handled_by_waiter_id');
    }
}
