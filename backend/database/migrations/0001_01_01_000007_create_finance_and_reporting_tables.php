<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_am');
            $table->string('code', 50)->unique();
            $table->timestamps();
        });

        // 2. Expenses (Operational expenditures: Rent, Utilities, Municipal, Equipment Maintenance)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('ETB');
            $table->text('description');
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'telebirr', 'check'])->default('cash');
            $table->string('receipt_voucher_number')->nullable();
            $table->string('receipt_attachment_url')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Daily Reports Snapshot
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('report_date');
            $table->unsignedInteger('total_orders_count')->default(0);
            $table->decimal('gross_sales', 12, 2)->default(0.00);
            $table->decimal('cash_sales', 12, 2)->default(0.00);
            $table->decimal('telebirr_sales', 12, 2)->default(0.00);
            $table->decimal('cbe_birr_sales', 12, 2)->default(0.00);
            $table->decimal('total_discounts', 12, 2)->default(0.00);
            $table->decimal('total_refunds', 12, 2)->default(0.00);
            $table->decimal('cost_of_goods_sold', 12, 2)->default(0.00); // COGS based on recipe depletion
            $table->decimal('gross_profit', 12, 2)->default(0.00);
            $table->decimal('total_expenses', 12, 2)->default(0.00);
            $table->decimal('estimated_net_profit', 12, 2)->default(0.00);
            $table->json('top_selling_products')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'report_date']);
        });

        // 4. Report Exports Log
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_type'); // daily_sales, inventory_audit, attendance, payroll
            $table->string('format', 10); // pdf, xlsx, csv
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
