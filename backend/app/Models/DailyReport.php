<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'branch_id',
        'report_date',
        'total_orders_count',
        'gross_sales',
        'cash_sales',
        'telebirr_sales',
        'cbe_birr_sales',
        'total_discounts',
        'total_refunds',
        'cost_of_goods_sold',
        'gross_profit',
        'total_expenses',
        'estimated_net_profit',
        'top_selling_products',
    ];

    protected $casts = [
        'report_date' => 'date',
        'gross_sales' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'telebirr_sales' => 'decimal:2',
        'cbe_birr_sales' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'total_refunds' => 'decimal:2',
        'cost_of_goods_sold' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'estimated_net_profit' => 'decimal:2',
        'top_selling_products' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
