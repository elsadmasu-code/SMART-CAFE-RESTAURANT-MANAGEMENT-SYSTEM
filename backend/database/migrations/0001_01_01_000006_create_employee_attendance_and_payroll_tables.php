<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_am');
            $table->timestamps();
        });

        // 2. Employees Master
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('employee_code', 30)->unique();
            $table->string('position');
            $table->date('hire_date');
            $table->decimal('base_salary', 12, 2)->default(0.00);
            $table->decimal('hourly_rate', 10, 2)->default(0.00);
            $table->string('bank_name')->nullable(); // e.g. Commercial Bank of Ethiopia (CBE)
            $table->string('bank_account_number')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->enum('employment_status', ['active', 'on_leave', 'probation', 'terminated'])->default('active');
            $table->timestamps();
        });

        // 3. Shifts
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('shift_name'); // Morning Shift (06:30 - 14:30), Evening Shift (14:30 - 22:30)
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('grace_period_minutes')->default(15);
            $table->timestamps();
        });

        // 4. Employee Shift Assignment
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('date');
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // 5. Attendance with Network/Wi-Fi Validation
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('attendance_date');
            $table->time('clock_in_time');
            $table->time('clock_out_time')->nullable();
            $table->string('clock_in_ip', 45);
            $table->string('clock_out_ip', 45)->nullable();
            $table->string('device_bssid', 50)->nullable();
            $table->enum('verification_method', ['wifi_network', 'device_signature', 'manager_manual_override'])->default('wifi_network');
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_departure')->default(false);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->enum('status', ['present', 'half_day', 'late', 'absent'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
        });

        // 6. Attendance Manual Corrections
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendance')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->time('corrected_clock_in')->nullable();
            $table->time('corrected_clock_out')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 7. Leave Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_am');
            $table->unsignedSmallInteger('annual_quota_days')->default(14);
            $table->boolean('is_paid')->default(true);
            $table->timestamps();
        });

        // 8. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('total_days');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('manager_comment')->nullable();
            $table->timestamps();
        });

        // 9. Payroll Periods (Monthly in Ethiopian Calendar)
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('period_name'); // e.g., "Hidar 2017 / November 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // 10. Payroll Items (Per employee payslip)
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('base_salary', 12, 2);
            $table->decimal('overtime_pay', 12, 2)->default(0.00);
            $table->decimal('allowances', 12, 2)->default(0.00);
            $table->decimal('bonuses', 12, 2)->default(0.00);
            $table->decimal('tax_deduction', 12, 2)->default(0.00); // Ethiopian income tax
            $table->decimal('pension_deduction', 12, 2)->default(0.00); // 7% employee pension
            $table->decimal('other_deductions', 12, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2);
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
