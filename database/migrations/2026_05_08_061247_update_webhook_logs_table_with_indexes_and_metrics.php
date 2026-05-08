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
            $table->uuid('delivery_id')->after('id')->nullable()->index();
            $table->integer('duration_ms')->after('response_body')->nullable();
            
            // Adding indexes for performance
            $table->index('event_name');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex(['delivery_id']);
            $table->dropIndex(['event_name']);
            $table->index(['status']);
            $table->index(['created_at']);
            
            $table->dropColumn(['delivery_id', 'duration_ms']);
        });
    }
};
