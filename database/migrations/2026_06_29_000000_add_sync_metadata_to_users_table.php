<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'sync_id')) {
                    $table->uuid('sync_id')->nullable()->unique()->index();
                    $table->integer('sync_version')->default(1);
                    $table->enum('sync_status', ['synced', 'pending', 'conflict'])->default('synced');
                    $table->timestamp('synced_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['sync_id', 'sync_version', 'sync_status', 'synced_at']);
            });
        }
    }
};
