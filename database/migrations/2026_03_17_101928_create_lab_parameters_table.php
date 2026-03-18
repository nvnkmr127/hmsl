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
        Schema::create('lab_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_test_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->nullable(); // mg/dL, g/L, etc.
            $table->string('reference_range')->nullable(); // e.g. 13.5 - 17.5
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_parameters');
    }
};
