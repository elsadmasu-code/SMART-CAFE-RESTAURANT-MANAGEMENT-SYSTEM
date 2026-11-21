<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_uuid',
        'branch_id',
        'order_number',
        'customer_id',
        'table_id',
        'order_type',
        'guest_name',
        'guest_phone',
        'delivery_address',
        'order_status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_charge',
        'total_amount',
        'notes',
        'confirmed_by_user_id',
        'confirmed_at',
        'cancellation_reason',
        'cancelled_at',
        'is_synced',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_synced' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }

    public function kitchenTickets()
    {
        return $this->hasMany(KitchenTicket::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function waiterAssignment()
    {
        return $this->hasOne(WaiterAssignment::class);
    }
}
