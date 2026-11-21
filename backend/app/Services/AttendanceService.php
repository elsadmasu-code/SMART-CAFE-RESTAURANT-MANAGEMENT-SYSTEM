<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Exception;

class AttendanceService
{
    /**
     * Validates whether the client's IP is within the cafe's registered local Wi-Fi subnet.
     */
    public function isAuthorizedLocalNetwork(string $clientIp): bool
    {
        // Allow local development loopback IPs
        if (in_array($clientIp, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        $configuredCidr = env('CAFE_WIFI_CIDR', '192.168.1.0/24');

        return $this->cidrMatch($clientIp, $configuredCidr)
            || $this->cidrMatch($clientIp, '10.0.0.0/8')
            || $this->cidrMatch($clientIp, '172.16.0.0/12');
    }

    /**
     * Records employee clock-in with network validation
     */
    public function clockIn(Employee $employee, Request $request, ?int $shiftId = null): Attendance
    {
        $clientIp = $request->ip();

        if (!$this->isAuthorizedLocalNetwork($clientIp)) {
            throw new Exception("Attendance clock-in only permitted on Fkr Cafe approved local Wi-Fi network (Your IP: {$clientIp})");
        }

        $today = now()->toDateString();
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if ($existing) {
            throw new Exception("Employee has already clocked in for today ({$today})");
        }

        $shift = $shiftId ? Shift::find($shiftId) : Shift::first();
        $isLate = false;

        if ($shift) {
            $shiftStartTime = now()->setTimeFromTimeString($shift->start_time);
            $graceTime = $shiftStartTime->copy()->addMinutes($shift->grace_period_minutes);
            if (now()->greaterThan($graceTime)) {
                $isLate = true;
            }
        }

        return Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift?->id,
            'attendance_date' => $today,
            'clock_in_time' => now()->toTimeString(),
            'clock_in_ip' => $clientIp,
            'device_bssid' => $request->input('device_bssid'),
            'verification_method' => 'wifi_network',
            'is_late' => $isLate,
            'status' => $isLate ? 'late' : 'present',
        ]);
    }

    /**
     * Records employee clock-out
     */
    public function clockOut(Employee $employee, Request $request): Attendance
    {
        $clientIp = $request->ip();

        if (!$this->isAuthorizedLocalNetwork($clientIp)) {
            throw new Exception("Attendance clock-out only permitted on Fkr Cafe local Wi-Fi network");
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance) {
            throw new Exception("No active clock-in found for today");
        }

        if ($attendance->clock_out_time) {
            throw new Exception("Employee has already clocked out for today");
        }

        // Calculate overtime if applicable
        $overtimeHours = 0.00;
        if ($attendance->shift) {
            $shiftEndTime = now()->setTimeFromTimeString($attendance->shift->end_time);
            if (now()->greaterThan($shiftEndTime)) {
                $diffMinutes = now()->diffInMinutes($shiftEndTime);
                $overtimeHours = round($diffMinutes / 60, 2);
            }
        }

        $attendance->update([
            'clock_out_time' => now()->toTimeString(),
            'clock_out_ip' => $clientIp,
            'overtime_hours' => $overtimeHours,
        ]);

        return $attendance;
    }

    protected function cidrMatch(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet);
        }

        return false;
    }
}
