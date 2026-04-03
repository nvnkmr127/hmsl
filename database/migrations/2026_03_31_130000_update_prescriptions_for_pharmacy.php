<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->boolean('is_dispensed')->default(false)->after('medicines');
            $table->dateTime('dispensed_at')->nullable()->after('is_dispensed');
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->after('dispensed_at');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['dispensed_by']);
            $table->dropColumn(['is_dispensed', 'dispensed_at', 'dispensed_by']);
        });
    }
};
