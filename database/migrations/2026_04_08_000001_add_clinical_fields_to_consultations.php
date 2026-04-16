<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->json('chief_complaints')->nullable()->after('notes');
            $table->text('history_of_present_illness')->nullable()->after('chief_complaints');
            $table->text('past_history')->nullable()->after('history_of_present_illness');
            $table->text('personal_history')->nullable()->after('past_history');
            $table->text('general_examination')->nullable()->after('personal_history');
            $table->text('systemic_examination')->nullable()->after('general_examination');
            $table->text('examination_findings')->nullable()->after('systemic_examination');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'chief_complaints',
                'history_of_present_illness',
                'past_history',
                'personal_history',
                'general_examination',
                'systemic_examination',
                'examination_findings',
                'created_by',
            ]);
        });
    }
};
