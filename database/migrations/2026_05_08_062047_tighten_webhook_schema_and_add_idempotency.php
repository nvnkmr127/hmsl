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
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->unique(['created_by', 'url'], 'unique_endpoint_per_user');
            $table->index('secret');
        });

        Schema::table('inbound_webhooks', function (Blueprint $table) {
            $table->string('external_id')->after('source')->nullable()->unique();
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->dropUnique('unique_endpoint_per_user');
            $table->dropIndex(['secret']);
        });

        Schema::table('inbound_webhooks', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropColumn('external_id');
            $table->dropIndex(['processed_at']);
        });
    }
};
