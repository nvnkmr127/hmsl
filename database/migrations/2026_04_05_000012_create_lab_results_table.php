<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');

            $table->text('result_values')->nullable();
            $table->text('result_notes')->nullable();
            $table->text('interpretations')->nullable();

            $table->enum('status', ['Pending', 'Partial', 'Completed', 'Cancelled'])->default('Pending');
            $table->boolean('is_abnormal')->default(false);
            $table->boolean('is_critical')->default(false);

            $table->dateTime('resulted_at')->nullable();
            $table->foreignId('resulted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['lab_order_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
