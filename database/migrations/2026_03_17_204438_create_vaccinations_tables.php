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
        Schema::create('vaccines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_disease')->nullable();
            $table->string('recommended_age')->nullable(); // e.g., "At Birth", "6 Weeks"
            $table->integer('sequence_order')->default(0);
            $table->timestamps();
        });

        Schema::create('patient_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('vaccine_id')->constrained()->onDelete('cascade');
            $table->date('date_given');
            $table->string('batch_number')->nullable();
            $table->string('administered_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_vaccinations');
        Schema::dropIfExists('vaccines');
    }
};
