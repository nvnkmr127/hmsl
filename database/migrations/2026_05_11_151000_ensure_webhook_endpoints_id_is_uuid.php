<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            // Fallback for non-mysql drivers
            if (Schema::hasTable('webhook_endpoints')) {
                Schema::table('webhook_endpoints', function (Blueprint $table) {
                    $table->uuid('id')->change();
                });
            }
            if (Schema::hasTable('webhook_logs')) {
                Schema::table('webhook_logs', function (Blueprint $table) {
                    $table->uuid('id')->change();
                });
            }
            return;
        }

        // MySQL Specific Hardening - Direct Force
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Fix webhook_endpoints
        if (Schema::hasTable('webhook_endpoints')) {
            // Force remove auto_increment and change type
            try {
                DB::statement('ALTER TABLE webhook_endpoints MODIFY id CHAR(36) NOT NULL');
            } catch (\Exception $e) {
                // If it fails (e.g. because of auto_increment), try removing it first
                DB::statement('ALTER TABLE webhook_endpoints MODIFY id BIGINT UNSIGNED');
                DB::statement('ALTER TABLE webhook_endpoints MODIFY id CHAR(36) NOT NULL');
            }
        }

        // 2. Fix webhook_logs (both primary id and foreign key)
        if (Schema::hasTable('webhook_logs')) {
            // Drop FK if it exists (wrap in try-catch in case it's already gone)
            try {
                DB::statement('ALTER TABLE webhook_logs DROP FOREIGN KEY webhook_logs_webhook_endpoint_id_foreign');
            } catch (\Exception $e) {}

            // Force fix primary id
            try {
                DB::statement('ALTER TABLE webhook_logs MODIFY id CHAR(36) NOT NULL');
            } catch (\Exception $e) {
                DB::statement('ALTER TABLE webhook_logs MODIFY id BIGINT UNSIGNED');
                DB::statement('ALTER TABLE webhook_logs MODIFY id CHAR(36) NOT NULL');
            }

            // Force fix foreign key
            DB::statement('ALTER TABLE webhook_logs MODIFY webhook_endpoint_id CHAR(36) NOT NULL');

            // Re-add FK
            DB::statement('ALTER TABLE webhook_logs ADD CONSTRAINT webhook_logs_webhook_endpoint_id_foreign FOREIGN KEY (webhook_endpoint_id) REFERENCES webhook_endpoints(id) ON DELETE CASCADE');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal is not safe as data conversion is destructive for primary keys
    }
};
