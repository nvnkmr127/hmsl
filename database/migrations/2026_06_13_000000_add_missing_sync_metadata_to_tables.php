<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $missingTables = [
        'doctors',
        'bill_items',
        'bill_payments',
        'bill_discounts',
        'prescription_items',
        'lab_results',
        'wards',
        'departments',
        'patient_vitals',
        'services',
        'diagnoses',
        'consultations',
    ];

    public function up(): void
    {
        foreach ($this->missingTables as $table) {
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
        foreach ($this->missingTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'sync_id')) {
                        $table->dropColumn(['sync_id', 'sync_version', 'sync_status', 'synced_at']);
                    }
                });
            }
        }
    }
};
