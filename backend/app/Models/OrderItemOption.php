<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemOption extends Model
{
    protected $fillable = [
        'order_item_id',
        'product_addon_id',
        'addon_name_snapshot',
        'price_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
