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
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->uuid('correlation_id')->after('delivery_id')->nullable()->index();
            $table->string('error_category', 50)->after('error_message')->nullable()->index();
        });

        Schema::table('webhook_outbox', function (Blueprint $table) {
            $table->uuid('correlation_id')->after('id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex(['correlation_id']);
            $table->dropIndex(['error_category']);
            $table->dropColumn(['correlation_id', 'error_category']);
        });

        Schema::table('webhook_outbox', function (Blueprint $table) {
            $table->dropIndex(['correlation_id']);
            $table->dropColumn('correlation_id');
        });
    }
};
