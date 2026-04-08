<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bill_payment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('admission_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('refund_number');
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->text('notes')->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Processed'])->default('Pending');

            $table->dateTime('processed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['bill_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
