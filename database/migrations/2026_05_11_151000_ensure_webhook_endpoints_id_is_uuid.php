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
        if (Schema::hasTable('webhook_endpoints')) {
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'mysql') {
                $columns = DB::select("SHOW COLUMNS FROM webhook_endpoints WHERE Field = 'id'");
                if (!empty($columns)) {
                    $column = $columns[0];
                    if (stripos($column->Type, 'int') !== false) {
                        // 1. Disable FK checks for the duration of this migration
                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                        // 2. Drop the foreign key constraint if it exists in webhook_logs
                        if (Schema::hasTable('webhook_logs')) {
                            // Check if the FK exists first to avoid error if it's already gone
                            $fks = DB::select("
                                SELECT CONSTRAINT_NAME 
                                FROM information_schema.KEY_COLUMN_USAGE 
                                WHERE TABLE_NAME = 'webhook_logs' 
                                AND CONSTRAINT_NAME = 'webhook_logs_webhook_endpoint_id_foreign'
                                AND TABLE_SCHEMA = DATABASE()
                            ");
                            
                            if (!empty($fks)) {
                                DB::statement('ALTER TABLE webhook_logs DROP FOREIGN KEY webhook_logs_webhook_endpoint_id_foreign');
                            }
                        }

                        // 3. Remove auto_increment if it exists
                        if (stripos($column->Extra, 'auto_increment') !== false) {
                            DB::statement('ALTER TABLE webhook_endpoints MODIFY id BIGINT UNSIGNED');
                        }
                        
                        // 4. Change the primary key to CHAR(36)
                        DB::statement('ALTER TABLE webhook_endpoints MODIFY id CHAR(36) NOT NULL');
                        
                        // 5. Change the foreign key in webhook_logs to CHAR(36)
                        if (Schema::hasTable('webhook_logs')) {
                             $logColumns = DB::select("SHOW COLUMNS FROM webhook_logs WHERE Field = 'webhook_endpoint_id'");
                             if (!empty($logColumns) && stripos($logColumns[0]->Type, 'int') !== false) {
                                 DB::statement('ALTER TABLE webhook_logs MODIFY webhook_endpoint_id CHAR(36) NOT NULL');
                             }
                             
                             // 6. Re-add the foreign key constraint
                             DB::statement('ALTER TABLE webhook_logs ADD CONSTRAINT webhook_logs_webhook_endpoint_id_foreign FOREIGN KEY (webhook_endpoint_id) REFERENCES webhook_endpoints(id) ON DELETE CASCADE');
                        }

                        // 7. Re-enable FK checks
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    }
                }
            } else {
                // Fallback for non-mysql drivers (using SQLite/Postgres standard approach)
                Schema::table('webhook_endpoints', function (Blueprint $table) {
                    $table->uuid('id')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal is not safe as data conversion is destructive for primary keys
    }
};
