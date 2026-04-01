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
        Schema::table('departments', function (Blueprint $table) {
            $table->string('code')->unique()->after('id')->nullable();
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('code')->unique()->after('id')->nullable();
        });
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->string('code')->unique()->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }

};
