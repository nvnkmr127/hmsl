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
        Schema::table('patients', function (Blueprint $table) {
            $table->text('allergies')->nullable()->after('blood_group');
            $table->string('insurance_provider')->nullable()->after('is_active');
            $table->string('insurance_policy')->nullable()->after('insurance_provider');
            $table->date('insurance_validity')->nullable()->after('insurance_policy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['allergies', 'insurance_provider', 'insurance_policy', 'insurance_validity']);
        });
    }
};
