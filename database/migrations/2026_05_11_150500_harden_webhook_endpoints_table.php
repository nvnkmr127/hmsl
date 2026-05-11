<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            // Rename columns to match the current WebhookEndpoint model and migrations
            if (Schema::hasColumn('webhook_endpoints', 'secret_key') && !Schema::hasColumn('webhook_endpoints', 'secret')) {
                $table->renameColumn('secret_key', 'secret');
            }
            
            if (Schema::hasColumn('webhook_endpoints', 'event_types') && !Schema::hasColumn('webhook_endpoints', 'events')) {
                $table->renameColumn('event_types', 'events');
            }

            // Ensure events column is json if it was renamed
            if (Schema::hasColumn('webhook_endpoints', 'events')) {
                $table->json('events')->change();
            }

            // Add missing columns that appear to be missing from the actual DB
            if (!Schema::hasColumn('webhook_endpoints', 'api_version')) {
                $table->string('api_version', 10)->default('v1')->after('is_active');
            }
            
            if (!Schema::hasColumn('webhook_endpoints', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('api_version')->constrained('users')->onDelete('set null');
            }
            
            // Clean up rogue columns if they exist
            if (Schema::hasColumn('webhook_endpoints', 'last_failed_at')) {
                $table->dropColumn('last_failed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            // No easy reversal as we are fixing a corrupted state
        });
    }
};
