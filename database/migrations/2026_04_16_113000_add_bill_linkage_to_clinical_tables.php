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
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->foreignId('bill_item_id')->nullable()->after('notes')->constrained()->onDelete('set null');
        });

        Schema::table('ipd_medication_charts', function (Blueprint $table) {
            $table->foreignId('bill_item_id')->nullable()->after('dispensed_at')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bill_item_id');
        });

        Schema::table('ipd_medication_charts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bill_item_id');
        });
    }
};
