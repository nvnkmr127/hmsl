<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::dropIfExists('procedure_charges');
        } catch (\Exception $e) {
            DB::statement('DROP TABLE IF EXISTS procedure_charges');
        }

        Schema::create('procedure_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('procedure_id')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('bill_item_id')->nullable()->constrained()->onDelete('set null');

            $table->string('procedure_name');
            $table->text('description')->nullable();
            $table->dateTime('performed_at')->nullable();
            $table->decimal('charge', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);

            $table->enum('status', ['Scheduled', 'Performed', 'Cancelled'])->default('Scheduled');

            $table->timestamps();

            $table->index(['admission_id']);
            $table->index(['procedure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_charges');
    }
};
