<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discharge_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('admission_number');
            $table->string('uhid');

            $table->dateTime('admission_date');
            $table->dateTime('discharge_date')->nullable();

            $table->text('admission_diagnosis')->nullable();
            $table->text('final_diagnosis')->nullable();
            $table->text('treatment_summary')->nullable();
            $table->text('procedures_done')->nullable();
            $table->text('investigations_summary')->nullable();

            $table->enum('condition_at_discharge', ['Stable', 'Improved', 'Critical', 'Referred', 'Expired', 'LAMA'])->nullable();
            $table->text('condition_notes')->nullable();

            $table->text('general_advice')->nullable();
            $table->text('diet_advice')->nullable();
            $table->text('activity_advice')->nullable();

            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();

            $table->enum('status', ['Draft', 'Review', 'Finalized'])->default('Draft');
            $table->boolean('is_finalized')->default(false);
            $table->dateTime('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['admission_id']);
            $table->index(['status']);
            $table->index(['is_finalized']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discharge_summaries');
    }
};
