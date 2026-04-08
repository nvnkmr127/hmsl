<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('slot_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('token_number')->nullable();

            $table->enum('type', ['New', 'Follow-up', 'Emergency'])->default('New');
            $table->enum('status', ['Scheduled', 'Checked-in', 'Completed', 'Cancelled', 'No-show'])->default('Scheduled');

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();

            $table->index(['patient_id']);
            $table->index(['doctor_id']);
            $table->index(['appointment_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
