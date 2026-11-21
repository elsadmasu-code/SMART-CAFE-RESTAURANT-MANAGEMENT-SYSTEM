<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenTicketItem extends Model
{
    protected $fillable = [
        'kitchen_ticket_id',
        'order_item_id',
        'item_name',
        'quantity',
        'notes',
        'is_ready',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_ready' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(KitchenTicket::class, 'kitchen_ticket_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
