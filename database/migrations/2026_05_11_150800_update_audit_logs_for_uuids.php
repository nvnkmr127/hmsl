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
        Schema::table('audit_logs', function (Blueprint $table) {
            // Change auditable_id to string to support models with UUIDs (like WebhookEndpoint)
            // We use string instead of char(36) to remain compatible with models using integer IDs
            $table->string('auditable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->bigInteger('auditable_id')->unsigned()->nullable()->change();
        });
    }
};
