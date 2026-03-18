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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('specialization');
            $table->string('qualification')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->string('registration_number')->nullable();
            $table->text('biography')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
