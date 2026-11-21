<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'department_id',
        'employee_code',
        'position',
        'hire_date',
        'base_salary',
        'hourly_rate',
        'bank_name',
        'bank_account_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'employment_status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'hire_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }
}
