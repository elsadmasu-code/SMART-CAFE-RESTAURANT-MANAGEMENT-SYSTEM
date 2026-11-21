<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaiterAssignment extends Model
{
    protected $fillable = [
        'order_id',
        'waiter_user_id',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_user_id');
    }
}
