<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * List all employees with departments & roles
     */
    public function index(Request $request)
    {
        $employees = Employee::with(['user.roles', 'department'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    /**
     * Register New Employee and User Account
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_slug' => 'required|exists:roles,slug',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'required|string',
            'base_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'branch_id' => $request->user()->branch_id ?? 1,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'preferred_language' => 'am',
            ]);

            $role = Role::where('slug', $request->role_slug)->firstOrFail();
            $user->roles()->attach($role->id);

            $employeeCode = 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            $employee = Employee::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'department_id' => $request->department_id,
                'employee_code' => $employeeCode,
                'position' => $request->position,
                'hire_date' => $request->hire_date,
                'base_salary' => $request->base_salary,
                'employment_status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully.',
                'data' => $employee->load('user.roles', 'department'),
            ], 201);
        });
    }

    /**
     * Submit Leave Request
     */
    public function requestLeave(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $employee = $request->user()->employee;
        if (!$employee) {
            abort(403, 'Authenticated user is not linked to an employee record.');
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted.',
            'data' => $leave,
        ], 201);
    }
}
