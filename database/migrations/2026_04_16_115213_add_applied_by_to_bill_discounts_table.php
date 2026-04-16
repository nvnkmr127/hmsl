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
        Schema::table('bill_discounts', function (Blueprint $table) {
            $table->foreignId('applied_by')->nullable()->constrained('users')->onDelete('set null');
            $table->index('applied_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_discounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('applied_by');
        });
    }
};
