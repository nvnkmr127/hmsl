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
        Schema::create('ipd_medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipd_medication_chart_id')->constrained('ipd_medication_charts')->onDelete('cascade');
            $table->foreignId('admission_id')->constrained('admissions')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('administering_nurse_id')->constrained('users');
            $table->dateTime('administered_at');
            $table->string('status')->default('Given'); // Given, Refused, Vomited, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_medication_administrations');
    }
};
