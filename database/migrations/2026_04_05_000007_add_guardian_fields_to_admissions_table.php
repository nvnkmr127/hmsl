<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('guardian_name')->nullable()->after('notes');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->string('guardian_relation')->nullable()->after('guardian_phone');
            $table->string('emergency_contact')->nullable()->after('guardian_relation');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['guardian_name', 'guardian_phone', 'guardian_relation', 'emergency_contact']);
        });
    }
};
