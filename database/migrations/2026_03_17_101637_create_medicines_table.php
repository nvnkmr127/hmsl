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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('generic_name')->nullable();
            $table->string('category')->index(); // Tablet, Capsule, Syrup, Injection, etc.
            $table->string('strength')->nullable(); // 500mg, 10ml, etc.
            $table->string('manufacturer')->nullable();
            $table->decimal('buying_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_level')->default(10);
            $table->date('expire_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
