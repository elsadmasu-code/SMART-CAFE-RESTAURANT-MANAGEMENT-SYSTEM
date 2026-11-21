<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenTicket extends Model
{
    protected $fillable = [
        'ticket_uuid',
        'order_id',
        'kitchen_station_id',
        'status',
        'target_preparation_time_minutes',
        'chef_user_id',
        'accepted_at',
        'preparing_at',
        'ready_at',
        'is_delayed_alert_sent',
    ];

    protected $casts = [
        'target_preparation_time_minutes' => 'integer',
        'accepted_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'is_delayed_alert_sent' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function station()
    {
        return $this->belongsTo(KitchenStation::class, 'kitchen_station_id');
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_user_id');
    }

    public function items()
    {
        return $this->hasMany(KitchenTicketItem::class);
    }
}
