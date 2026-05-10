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
            // Drop index if it exists (MySQL fails to change column to TEXT if index exists)
            $table->dropIndex('webhook_endpoints_secret_index');
            $table->text('secret')->change();
        });

        Schema::table('webhook_sources', function (Blueprint $table) {
            $table->text('secret')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->string('secret', 255)->change();
            $table->index('secret');
        });

        Schema::table('webhook_sources', function (Blueprint $table) {
            $table->string('secret', 255)->change();
        });
    }
};
