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
        Schema::table('inbound_webhooks', function (Blueprint $table) {
            if (!Schema::hasColumn('inbound_webhooks', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('status')->index();
            }
            if (!Schema::hasColumn('inbound_webhooks', 'attempt_count')) {
                $table->integer('attempt_count')->default(0)->after('is_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_webhooks', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'attempt_count']);
        });
    }
};
