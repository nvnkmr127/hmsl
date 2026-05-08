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
        Schema::create('webhook_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index();
            $table->json('payload');
            $table->enum('status', ['pending', 'processing', 'dispatched', 'failed'])->default('pending')->index();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_outbox');
    }
};
