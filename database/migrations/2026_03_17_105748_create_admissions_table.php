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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->constrained()->onDelete('restrict');
            $table->foreignId('doctor_id')->constrained()->onDelete('restrict');
            $table->dateTime('admission_date');
            $table->dateTime('discharge_date')->nullable();
            $table->string('reason_for_admission')->nullable();
            $table->string('status')->default('Admitted'); // Admitted, Discharged, Transferred
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
