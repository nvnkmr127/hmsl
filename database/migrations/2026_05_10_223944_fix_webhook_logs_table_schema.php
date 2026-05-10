<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            // Drop rogue columns if they exist
            if (Schema::hasColumn('webhook_logs', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('webhook_logs', 'message')) {
                $table->dropColumn('message');
            }
            if (Schema::hasColumn('webhook_logs', 'context')) {
                $table->dropColumn('context');
            }

            // Add missing columns required by the model and service
            if (!Schema::hasColumn('webhook_logs', 'delivery_id')) {
                $table->uuid('delivery_id')->after('id')->nullable()->index();
            }
            if (!Schema::hasColumn('webhook_logs', 'correlation_id')) {
                $table->uuid('correlation_id')->after('delivery_id')->nullable()->index();
            }
            if (!Schema::hasColumn('webhook_logs', 'event_name')) {
                $table->string('event_name')->after('correlation_id');
            }
            if (!Schema::hasColumn('webhook_logs', 'payload')) {
                $table->longText('payload')->after('event_name');
            }
            if (!Schema::hasColumn('webhook_logs', 'request_headers')) {
                $table->json('request_headers')->nullable()->after('payload');
            }
            if (!Schema::hasColumn('webhook_logs', 'response_status')) {
                $table->integer('response_status')->nullable()->after('request_headers');
            }
            if (!Schema::hasColumn('webhook_logs', 'response_body')) {
                $table->longText('response_body')->nullable()->after('response_status');
            }
            if (!Schema::hasColumn('webhook_logs', 'response_headers')) {
                $table->json('response_headers')->nullable()->after('response_body');
            }
            if (!Schema::hasColumn('webhook_logs', 'duration_ms')) {
                $table->integer('duration_ms')->nullable()->after('response_headers');
            }
            if (!Schema::hasColumn('webhook_logs', 'attempt_number')) {
                $table->integer('attempt_number')->default(1)->after('duration_ms');
            }
            if (!Schema::hasColumn('webhook_logs', 'status')) {
                $table->string('status')->default('pending')->after('attempt_number')->index();
            }
            if (!Schema::hasColumn('webhook_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('status');
            }
            if (!Schema::hasColumn('webhook_logs', 'error_category')) {
                $table->string('error_category')->nullable()->after('error_message')->index();
            }
            if (!Schema::hasColumn('webhook_logs', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('error_category');
            }
            if (!Schema::hasColumn('webhook_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // No easy rollback
    }
};
