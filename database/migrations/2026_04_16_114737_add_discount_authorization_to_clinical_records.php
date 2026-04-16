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
        Schema::table('consultations', function (Blueprint $table) {
            $table->boolean('is_discount_authorized')->default(false);
            $table->decimal('authorized_discount_limit', 10, 2)->default(0);
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->boolean('is_discount_authorized')->default(false);
            $table->decimal('authorized_discount_limit', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['is_discount_authorized', 'authorized_discount_limit']);
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['is_discount_authorized', 'authorized_discount_limit']);
        });
    }
};
