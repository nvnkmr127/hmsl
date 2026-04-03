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
        Schema::create('inbound_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('source')->index(); // e.g. 'stripe', 'custom', 'external_lab'
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('status')->default('pending')->index(); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_webhooks');
    }
};
