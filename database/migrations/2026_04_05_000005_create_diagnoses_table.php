<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('admission_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('consultation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('diagnosis_name');
            $table->string('icd_code')->nullable();
            $table->enum('type', ['Primary', 'Secondary', 'Comorbidity', 'Chief Complaint', 'Final'])->default('Primary');
            $table->enum('status', ['Suspected', 'Confirmed', 'Ruled Out'])->default('Confirmed');

            $table->text('notes')->nullable();

            $table->date('diagnosed_date')->nullable();
            $table->date('resolved_date')->nullable();

            $table->timestamps();

            $table->index(['patient_id']);
            $table->index(['admission_id']);
            $table->index(['consultation_id']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
