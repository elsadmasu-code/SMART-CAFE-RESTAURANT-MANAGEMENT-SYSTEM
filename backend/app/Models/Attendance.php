<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'attendance_date',
        'clock_in_time',
        'clock_out_time',
        'clock_in_ip',
        'clock_out_ip',
        'device_bssid',
        'verification_method',
        'is_late',
        'is_early_departure',
        'overtime_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_late' => 'boolean',
        'is_early_departure' => 'boolean',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
