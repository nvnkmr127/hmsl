<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inbound_webhooks', function (Blueprint $table) {
            // Rename legacy columns if they exist
            if (Schema::hasColumn('inbound_webhooks', 'source_provider')) {
                $table->renameColumn('source_provider', 'source');
            }
            if (Schema::hasColumn('inbound_webhooks', 'processing_status')) {
                $table->renameColumn('processing_status', 'status');
            }
            if (Schema::hasColumn('inbound_webhooks', 'error_details')) {
                $table->renameColumn('error_details', 'error_message');
            }

            // Add missing columns
            if (!Schema::hasColumn('inbound_webhooks', 'external_id')) {
                $table->string('external_id')->after('source')->nullable()->unique();
            }
            if (!Schema::hasColumn('inbound_webhooks', 'correlation_id')) {
                $table->uuid('correlation_id')->after('external_id')->nullable()->index();
            }
            if (!Schema::hasColumn('inbound_webhooks', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('status')->index();
            }
            if (!Schema::hasColumn('inbound_webhooks', 'attempt_count')) {
                $table->integer('attempt_count')->default(0)->after('is_verified');
            }
            if (!Schema::hasColumn('inbound_webhooks', 'error_category')) {
                $table->string('error_category')->nullable()->after('error_message')->index();
            }
            
            // Fix types if necessary
            $table->json('payload')->change();
            $table->json('headers')->nullable()->change();
        });
    }

    public function down(): void
    {
        // No easy rollback for renames and conditional adds
    }
};
