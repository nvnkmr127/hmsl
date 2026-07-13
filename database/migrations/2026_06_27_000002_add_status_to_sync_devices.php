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
        Schema::table('sync_devices', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
            
            if (!Schema::hasColumn('sync_devices', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
            if (!Schema::hasColumn('sync_devices', 'suspended_reason')) {
                $table->text('suspended_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_devices', function (Blueprint $table) {
            if (Schema::hasColumn('sync_devices', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
            if (Schema::hasColumn('sync_devices', 'suspended_reason')) {
                $table->dropColumn('suspended_reason');
            }
        });
    }
};
