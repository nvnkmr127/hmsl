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
            $table->index(['status', 'event_name']);
            // If there are duplicate delivery_ids, we might need to handle them,
            // but for a fresh production-ready state, we want this unique.
            // $table->unique('delivery_id'); 
        });

        Schema::table('webhook_outbox', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex(['status', 'event_name']);
        });

        Schema::table('webhook_outbox', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
