<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. AI Conversations (Multi-role assistance sessions)
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('conversation_uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('role_context', ['customer', 'cashier', 'chef', 'waiter', 'storekeeper', 'manager'])->default('customer');
            $table->string('session_title')->nullable();
            $table->string('locale', 5)->default('am'); // 'am' or 'en'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. AI Messages
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->enum('sender', ['user', 'assistant', 'system'])->default('user');
            $table->text('message');
            $table->json('suggested_actions')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // 3. AI Usage & Audit
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('model_name')->default('gemini-1.5-flash');
            $table->string('endpoint');
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('latency_seconds', 6, 3)->default(0.000);
            $table->string('status_code', 10)->default('200');
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Synchronization Events (Local Edge to Cloud Event Sourcing)
        Schema::create('sync_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('aggregate_type'); // 'Order', 'Payment', 'Attendance', 'StockMovement'
            $table->string('aggregate_uuid');
            $table->string('event_type'); // 'OrderCreated', 'PaymentVerified', 'AttendanceLogged'
            $table->json('payload');
            $table->enum('sync_status', ['pending', 'synced', 'conflict', 'failed'])->default('pending');
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 5. Synchronization Conflicts
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_event_id')->constrained('sync_events')->cascadeOnDelete();
            $table->text('conflict_description');
            $table->json('local_payload');
            $table->json('remote_payload');
            $table->enum('resolution_status', ['pending', 'resolved_local', 'resolved_remote', 'resolved_manual'])->default('pending');
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 6. Idempotency Records (Prevents double payment & double order deduction)
        Schema::create('idempotency_records', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key', 128)->unique();
            $table->string('endpoint');
            $table->string('request_hash', 64);
            $table->json('response_body');
            $table->unsignedSmallInteger('response_status_code')->default(200);
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Backup Logs
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('backup_filename');
            $table->unsignedBigInteger('file_size_bytes');
            $table->enum('storage_type', ['local_disk', 'cloud_s3', 'usb_drive'])->default('local_disk');
            $table->boolean('is_encrypted')->default(true);
            $table->boolean('verification_passed')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('idempotency_records');
        Schema::dropIfExists('sync_conflicts');
        Schema::dropIfExists('sync_events');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
