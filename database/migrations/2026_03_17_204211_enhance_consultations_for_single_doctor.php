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
        Schema::table('consultations', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable()->after('doctor_id');
            $table->decimal('temperature', 8, 2)->nullable()->after('weight');
            $table->date('valid_upto')->nullable()->after('consultation_date');
            $table->string('payment_method')->default('Cash')->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['weight', 'temperature', 'valid_upto', 'payment_method']);
        });
    }
};
