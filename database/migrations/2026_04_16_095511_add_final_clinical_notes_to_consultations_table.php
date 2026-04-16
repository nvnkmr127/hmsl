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
            $table->text('diagnosis_notes')->nullable()->after('examination_findings');
            $table->text('advice')->nullable()->after('diagnosis_notes');
            $table->date('follow_up_date')->nullable()->after('advice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['diagnosis_notes', 'advice', 'follow_up_date']);
        });
    }
};
