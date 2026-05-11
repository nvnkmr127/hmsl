<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cron_job_runs', function (Blueprint $table) {
            $table->id();
            $table->string('job_key', 100);
            $table->string('command', 255);
            $table->string('status', 20)->default('running');
            $table->integer('exit_code')->nullable();
            $table->text('error_message')->nullable();
            $table->string('output_path', 500)->nullable();
            $table->string('host', 255)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['job_key', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_job_runs');
    }
};
