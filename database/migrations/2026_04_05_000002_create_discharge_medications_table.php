<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discharge_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discharge_summary_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->nullable()->constrained()->onDelete('set null');

            $table->string('medicine_name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->string('route')->default('Oral');
            $table->text('instructions')->nullable();

            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();

            $table->boolean('is_continued')->default(false);

            $table->timestamps();

            $table->index(['discharge_summary_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discharge_medications');
    }
};
