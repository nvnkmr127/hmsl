<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('admission_id')->nullable()->after('consultation_id')->constrained()->nullOnDelete();
            $table->unique('admission_id');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropUnique(['admission_id']);
            $table->dropConstrainedForeignId('admission_id');
        });
    }
};

