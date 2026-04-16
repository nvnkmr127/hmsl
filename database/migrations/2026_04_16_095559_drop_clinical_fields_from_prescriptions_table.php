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
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['chief_complaint', 'diagnosis', 'advice', 'follow_up_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('advice')->nullable();
            $table->date('follow_up_date')->nullable();
        });
    }
};
