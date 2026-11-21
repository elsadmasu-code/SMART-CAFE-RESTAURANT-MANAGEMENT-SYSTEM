<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Customer Profiles
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('phone', 30)->unique();
            $table->string('email')->nullable();
            $table->string('preferred_language', 5)->default('am');
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->decimal('total_spend', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Customer Delivery Addresses
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->string('city')->default('Kombolcha');
            $table->string('kebelle')->nullable();
            $table->string('house_number')->nullable();
            $table->string('landmark')->nullable();
            $table->string('phone', 30);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 3. Customer Preferences & Dietary
        Schema::create('customer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->string('dietary_type')->default('standard'); // standard, fasting_orthodox, vegetarian
            $table->boolean('prefers_spicy')->default(true);
            $table->boolean('prefers_sugar_free')->default(false);
            $table->json('favorite_product_ids')->nullable();
            $table->timestamps();
        });

        // 4. Dining Tables
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('table_number', 20);
            $table->unsignedSmallInteger('seating_capacity')->default(4);
            $table->string('zone')->default('Main Hall'); // Main Hall, Garden, Balcony, VIP Lounge
            $table->enum('status', ['vacant', 'occupied', 'reserved', 'cleaning'])->default('vacant');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'table_number']);
        });

        // 5. QR Codes for Tables
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->string('signed_token', 64)->unique();
            $table->string('qr_url');
            $table->unsignedBigInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
        });

        // 6. Table Sessions
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_uuid')->unique();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->foreignId('customer_profile_id')->nullable()->constrained('customer_profiles')->nullOnDelete();
            $table->foreignId('assigned_waiter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 7. Website Analytics / Discovery visits
        Schema::create('website_visits', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->default('direct'); // direct, google_search, qr_table, shared_link, nfc
            $table->string('landing_page')->nullable();
            $table->string('table_reference', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_visits');
        Schema::dropIfExists('table_sessions');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('dining_tables');
        Schema::dropIfExists('customer_preferences');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
    }
};
