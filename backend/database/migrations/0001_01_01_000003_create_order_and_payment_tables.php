<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_uuid')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('order_number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('dining_tables')->nullOnDelete();
            $table->enum('order_type', ['dine_in', 'takeaway', 'delivery'])->default('dine_in');
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 30)->nullable();
            $table->string('delivery_address')->nullable();
            $table->enum('order_status', [
                'draft',
                'submitted',
                'awaiting_payment',
                'payment_verification_pending',
                'confirmed',
                'accepted_by_kitchen',
                'preparing',
                'ready',
                'assigned_to_waiter',
                'delivered',
                'completed',
                'cancelled',
                'refunded'
            ])->default('submitted');
            $table->enum('payment_status', ['pending', 'payment_verification_pending', 'paid', 'partially_paid', 'refunded'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('service_charge', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('is_synced')->default(true);
            $table->timestamps();
        });

        // 2. Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->decimal('unit_price_snapshot', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });

        // 3. Order Item Options (Addons selected)
        Schema::create('order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_addon_id')->nullable()->constrained('product_addons')->nullOnDelete();
            $table->string('addon_name_snapshot');
            $table->decimal('price_snapshot', 12, 2);
            $table->timestamps();
        });

        // 4. Order Status History
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 5. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('payment_uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'telebirr', 'cbe_birr', 'chapa'])->default('cash');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('ETB');
            $table->string('transaction_reference', 100)->nullable();
            $table->enum('status', ['pending', 'verification_pending', 'verified', 'failed', 'refunded'])->default('pending');
            $table->string('proof_image_url')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });

        // 6. Receipts
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method_snapshot', 50);
            $table->unsignedInteger('printed_count')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Cash Registers (POS Drawer Sessions)
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->decimal('cash_received', 12, 2)->default(0.00);
            $table->decimal('cash_paid_out', 12, 2)->default(0.00);
            $table->decimal('expected_closing_balance', 12, 2)->default(0.00);
            $table->decimal('actual_closing_balance', 12, 2)->nullable();
            $table->decimal('discrepancy', 12, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();
        });

        // 8. Cash Transactions (Petty Cash in/out)
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->enum('transaction_type', ['cash_in', 'cash_out']);
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->foreignId('authorized_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_item_options');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
