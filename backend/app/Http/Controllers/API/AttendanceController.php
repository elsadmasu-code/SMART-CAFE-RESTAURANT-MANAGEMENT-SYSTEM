<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Exception;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Employee Clock-In (Local Network Restricted)
     */
    public function clockIn(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have an employee profile.',
            ], 403);
        }

        try {
            $attendance = $this->attendanceService->clockIn($employee, $request);

            return response()->json([
                'success' => true,
                'message' => 'Clock-in verified on Fkr Cafe local network.',
                'data' => $attendance,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Employee Clock-Out
     */
    public function clockOut(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have an employee profile.',
            ], 403);
        }

        try {
            $attendance = $this->attendanceService->clockOut($employee, $request);

            return response()->json([
                'success' => true,
                'message' => 'Clock-out successfully recorded.',
                'data' => $attendance,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get Attendance Log for Current Day or Date Range
     */
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $records = Attendance::with(['employee.user', 'employee.department', 'shift'])
            ->where('attendance_date', $date)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }
}
