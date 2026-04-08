<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');

            $table->dateTime('recorded_at');

            $table->decimal('bp_systolic', 5, 2)->nullable();
            $table->decimal('bp_diastolic', 5, 2)->nullable();
            $table->string('bp')->nullable();

            $table->decimal('pulse', 5, 2)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('spo2', 4, 1)->nullable();
            $table->decimal('resp_rate', 4, 1)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();

            $table->string('pain_scale', 10)->nullable();
            $table->string('glasgow_coma_scale', 10)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['admission_id']);
            $table->index(['recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_vitals');
    }
};
