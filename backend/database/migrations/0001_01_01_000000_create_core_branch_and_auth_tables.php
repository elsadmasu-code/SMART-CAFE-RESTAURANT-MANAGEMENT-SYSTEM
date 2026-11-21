<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Branches
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('city')->default('Kombolcha');
            $table->string('region')->default('Amhara');
            $table->string('country')->default('Ethiopia');
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('tin_number', 50)->nullable();
            $table->string('currency_code', 10)->default('ETB');
            $table->decimal('default_vat_rate', 5, 2)->default(15.00);
            $table->decimal('default_service_charge', 5, 2)->default(5.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 30)->unique()->nullable();
            $table->string('password');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('preferred_language', 5)->default('am'); // 'am' or 'en'
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // 'manager', 'cashier', 'chef', 'waiter', 'storekeeper', 'owner', 'customer'
            $table->string('name_en');
            $table->string('name_am');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_en');
            $table->string('module');
            $table->timestamps();
        });

        // 5. Role Permissions pivot
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        // 6. User Roles pivot
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->unique(['user_id', 'role_id']);
        });

        // 7. Personal Access Tokens (Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 8. Registered Devices
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_uuid')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_name');
            $table->enum('device_type', ['pos_terminal', 'kds_screen', 'waiter_handset', 'manager_laptop', 'customer_tablet']);
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->boolean('is_authorized')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // 9. System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, integer, float
            $table->timestamps();
        });

        // 10. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('payload_diff')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
    }
};
