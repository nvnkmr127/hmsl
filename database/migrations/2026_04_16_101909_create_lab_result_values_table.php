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
        Schema::create('lab_result_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('lab_parameter_id')->constrained()->onDelete('cascade');
            $table->string('result_value');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->dateTime('captured_at');
            $table->timestamps();
            
            $table->index(['patient_id', 'lab_parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_result_values');
    }
};
