<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'patients', 
        'appointments', 
        'bills', 
        'pharmacy_stocks', 
        'medicines', 
        'lab_orders', 
        'prescriptions',
        'admissions',
        'beds',
        'inventory_items'
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'sync_id')) {
                        $table->uuid('sync_id')->nullable()->unique()->index();
                        $table->integer('sync_version')->default(1);
                        $table->enum('sync_status', ['synced', 'pending', 'conflict'])->default('synced');
                        $table->timestamp('synced_at')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['sync_id', 'sync_version', 'sync_status', 'synced_at']);
                });
            }
        }
    }
};
