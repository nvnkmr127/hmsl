<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('note_type', ['Doctor', 'Nurse', 'Procedure', 'Progress', 'Emergency', 'Death']);
            $table->dateTime('note_date');
            $table->text('content');

            $table->boolean('is_editable')->default(true);
            $table->dateTime('locked_at')->nullable();
            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            $table->index(['admission_id']);
            $table->index(['note_type']);
            $table->index(['note_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_notes');
    }
};
