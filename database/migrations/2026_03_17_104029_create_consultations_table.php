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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->integer('token_number');
            $table->date('consultation_date')->index();
            $table->decimal('fee', 10, 2);
            $table->string('status')->default('Pending')->index(); // Pending, Ongoing, Completed, Cancelled
            $table->string('payment_status')->default('Unpaid')->index(); // Paid, Unpaid
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Per doctor per day token should be unique (conceptually, but we just increment)
            $table->index(['doctor_id', 'consultation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
