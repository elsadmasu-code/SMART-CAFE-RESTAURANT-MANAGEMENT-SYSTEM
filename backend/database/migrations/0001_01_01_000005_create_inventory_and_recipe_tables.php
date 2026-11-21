<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inventory Storage Locations
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name'); // Main Dry Store, Cold Room, Bar Stock, Kitchen Pantry
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('city')->default('Kombolcha');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Ingredients (Raw materials: Flour, Coffee beans, Oil, Cheese, Meat, Sugar, Milk, etc.)
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('inventory_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->string('name_en');
            $table->string('name_am');
            $table->string('sku', 50)->unique()->nullable();
            $table->string('unit_of_measure', 20); // kg, g, liter, ml, piece, packet
            $table->decimal('current_stock', 12, 3)->default(0.000);
            $table->decimal('minimum_stock_level', 12, 3)->default(5.000);
            $table->decimal('reorder_level', 12, 3)->default(10.000);
            $table->decimal('cost_per_unit', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Recipes (Bill of Materials header)
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->string('recipe_name');
            $table->unsignedInteger('yield_quantity')->default(1);
            $table->text('preparation_notes')->nullable();
            $table->timestamps();
        });

        // 5. Recipe Items (Formula ingredients)
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantity_required', 12, 3); // in ingredient's unit of measure
            $table->timestamps();
        });

        // 6. Purchases (Incoming inventory replenishment)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('invoice_number', 50)->nullable();
            $table->date('purchase_date');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'credit', 'partial'])->default('paid');
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Purchase Items
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantity_received', 12, 3);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 8. Stock Movements (Immutable Inventory Ledger)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->enum('movement_type', ['purchase', 'order_consumption', 'order_reversal', 'wastage', 'manual_adjustment']);
            $table->decimal('quantity_change', 12, 3); // positive for addition, negative for deduction
            $table->decimal('balance_after', 12, 3);
            $table->string('reference_type')->nullable(); // 'Order', 'Purchase', 'WastageRecord'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 9. Wastage Records (Spoilage, expiration, breakage)
        Schema::create('wastage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('wasted_quantity', 12, 3);
            $table->decimal('estimated_cost_loss', 12, 2);
            $table->string('reason_code'); // expired, burned, spilled, spoiled
            $table->foreignId('reported_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Stock Count Audits
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('audit_name');
            $table->date('audit_date');
            $table->enum('status', ['in_progress', 'completed', 'discrepancy_resolved'])->default('in_progress');
            $table->foreignId('conducted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('system_quantity', 12, 3);
            $table->decimal('counted_quantity', 12, 3);
            $table->decimal('variance', 12, 3);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('wastage_records');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('inventory_locations');
    }
};
