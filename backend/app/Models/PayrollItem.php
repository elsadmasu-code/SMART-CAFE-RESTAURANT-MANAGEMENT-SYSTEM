<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'base_salary',
        'overtime_pay',
        'allowances',
        'bonuses',
        'tax_deduction',
        'pension_deduction',
        'other_deductions',
        'net_salary',
        'payment_status',
        'payment_reference',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'pension_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
