<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') return;

        // Force convert webhook tables to UUID format (CHAR 36)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 0. PRE-EMPTIVE: Drop FK if it exists to allow column modification in BOTH tables
        if (Schema::hasTable('webhook_logs')) {
            try {
                DB::statement('ALTER TABLE webhook_logs DROP FOREIGN KEY webhook_logs_webhook_endpoint_id_foreign');
            } catch (\Exception $e) {}
        }

        // 1. Force Endpoints ID
        if (Schema::hasTable('webhook_endpoints')) {
            try {
                // Attempt direct conversion first
                DB::statement('ALTER TABLE webhook_endpoints MODIFY id CHAR(36) NOT NULL');
            } catch (\Exception $e) {
                // If it fails (likely due to existing auto-increment), strip it first
                DB::statement('ALTER TABLE webhook_endpoints MODIFY id BIGINT UNSIGNED');
                DB::statement('ALTER TABLE webhook_endpoints MODIFY id CHAR(36) NOT NULL');
            }
        }

        // 2. Force Logs ID and Foreign Key
        if (Schema::hasTable('webhook_logs')) {
            // Fix primary id of the logs table
            try {
                DB::statement('ALTER TABLE webhook_logs MODIFY id CHAR(36) NOT NULL');
            } catch (\Exception $e) {
                DB::statement('ALTER TABLE webhook_logs MODIFY id BIGINT UNSIGNED');
                DB::statement('ALTER TABLE webhook_logs MODIFY id CHAR(36) NOT NULL');
            }

            // Fix the foreign key reference
            DB::statement('ALTER TABLE webhook_logs MODIFY webhook_endpoint_id CHAR(36) NOT NULL');
            
            // Re-establish the foreign key link
            try {
                DB::statement('ALTER TABLE webhook_logs ADD CONSTRAINT webhook_logs_webhook_endpoint_id_foreign FOREIGN KEY (webhook_endpoint_id) REFERENCES webhook_endpoints(id) ON DELETE CASCADE');
            } catch (\Exception $e) {}
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Conversion to UUID is non-reversible without data loss
    }
};
