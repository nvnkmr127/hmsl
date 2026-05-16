<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Devices table
        Schema::create('sync_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_uuid')->unique();
            $table->string('name')->nullable();
            $table->string('os_version')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('status', ['active', 'blocked', 'pending'])->default('active');
            $table->timestamps();
        });

        // 2. Outbox (Change tracking for offline -> online)
        Schema::create('sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_id')->index();
            $table->string('table_name');
            $table->uuid('record_uuid');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('payload');
            $table->integer('sync_version');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });

        // 3. Conflict Log
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->uuid('record_uuid');
            $table->json('local_data');
            $table->json('server_data');
            $table->enum('resolution', ['pending', 'local_wins', 'server_wins', 'manual'])->default('pending');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 4. Sync Audit Log
        Schema::create('sync_audit_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_id');
            $table->string('action');
            $table->json('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_audit_log');
        Schema::dropIfExists('sync_conflicts');
        Schema::dropIfExists('sync_outbox');
        Schema::dropIfExists('sync_devices');
    }
};
