<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_medication_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('prescribed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');

            $table->string('medicine_name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('route')->default('Oral');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->text('instructions')->nullable();

            $table->enum('status', ['Active', 'Stopped', 'Completed', 'Cancelled'])->default('Active');
            $table->text('stop_reason')->nullable();
            $table->dateTime('stopped_at')->nullable();
            $table->foreignId('stopped_by')->nullable()->constrained('users')->onDelete('set null');

            $table->boolean('is_dispensed')->default(false);
            $table->dateTime('dispensed_at')->nullable();

            $table->timestamps();

            $table->index(['admission_id']);
            $table->index(['status']);
            $table->index(['medicine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_medication_charts');
    }
};
