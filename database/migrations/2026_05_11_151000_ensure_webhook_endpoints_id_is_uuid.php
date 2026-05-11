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
        // Force the ID column to be CHAR(36) to support UUIDs if it was created as an Integer
        if (Schema::hasTable('webhook_endpoints')) {
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'mysql') {
                $columns = DB::select("SHOW COLUMNS FROM webhook_endpoints WHERE Field = 'id'");
                if (!empty($columns)) {
                    $column = $columns[0];
                    if (stripos($column->Type, 'int') !== false) {
                        // 1. Remove auto_increment if it exists
                        if (stripos($column->Extra, 'auto_increment') !== false) {
                            DB::statement('ALTER TABLE webhook_endpoints MODIFY id BIGINT UNSIGNED');
                        }
                        
                        // 2. Change to CHAR(36)
                        DB::statement('ALTER TABLE webhook_endpoints MODIFY id CHAR(36) NOT NULL');
                        
                        // 3. Ensure other related tables are also updated if they exist
                        if (Schema::hasTable('webhook_logs')) {
                             $logColumns = DB::select("SHOW COLUMNS FROM webhook_logs WHERE Field = 'webhook_endpoint_id'");
                             if (!empty($logColumns) && stripos($logColumns[0]->Type, 'int') !== false) {
                                 DB::statement('ALTER TABLE webhook_logs MODIFY webhook_endpoint_id CHAR(36) NOT NULL');
                             }
                        }
                    }
                }
            } else {
                // Fallback for non-mysql drivers
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
