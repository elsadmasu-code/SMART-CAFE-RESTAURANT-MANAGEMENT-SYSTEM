<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kitchen Stations (e.g. Barista/Coffee, Traditional Grill, Fast Food & Pizza, Bakery)
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_am');
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Kitchen Tickets
        Schema::create('kitchen_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('kitchen_station_id')->nullable()->constrained('kitchen_stations')->nullOnDelete();
            $table->enum('status', ['new', 'accepted', 'preparing', 'ready', 'delayed', 'cancelled'])->default('new');
            $table->unsignedSmallInteger('target_preparation_time_minutes')->default(15);
            $table->foreignId('chef_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->boolean('is_delayed_alert_sent')->default(false);
            $table->timestamps();
        });

        // 3. Kitchen Ticket Items
        Schema::create('kitchen_ticket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_ticket_id')->constrained('kitchen_tickets')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->boolean('is_ready')->default(false);
            $table->timestamps();
        });

        // 4. Waiter Assignments
        Schema::create('waiter_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->enum('status', ['assigned', 'picked_up', 'delivered'])->default('assigned');
            $table->timestamps();
        });

        // 5. Customer Service Calls (Call Waiter / Table Requests)
        Schema::create('service_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->enum('request_type', ['waiter_call', 'water', 'bill', 'napkins', 'clean_table', 'custom'])->default('waiter_call');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'acknowledged', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('handled_by_waiter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 6. System Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('role_target', 50)->nullable(); // e.g. 'waiter', 'cashier', 'chef', 'manager'
            $table->string('title');
            $table->text('body');
            $table->string('type', 50)->default('info'); // order_ready, service_call, low_stock, payment_received
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('service_calls');
        Schema::dropIfExists('waiter_assignments');
        Schema::dropIfExists('kitchen_ticket_items');
        Schema::dropIfExists('kitchen_tickets');
        Schema::dropIfExists('kitchen_stations');
    }
};
