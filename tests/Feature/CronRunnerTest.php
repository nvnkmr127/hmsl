<?php

namespace Tests\Feature;

use App\Events\System\CronJobFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CronRunnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_job_key_returns_exit_code_2()
    {
        $this->artisan('hms:cron-run', ['job' => 'does-not-exist'])
            ->assertExitCode(2);
    }

    public function test_cron_run_records_successful_run()
    {
        Config::set('cron.jobs.test-job', [
            'enabled' => true,
            'cron' => '* * * * *',
            'command' => 'inspire',
            'args' => [],
        ]);

        $this->artisan('hms:cron-run', ['job' => 'test-job'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('cron_job_runs', [
            'job_key' => 'test-job',
            'status' => 'succeeded',
        ]);
    }

    public function test_cron_run_dispatches_failure_event_and_records_failed_run()
    {
        Event::fake([CronJobFailed::class]);

        Config::set('cron.jobs.bad-job', [
            'enabled' => true,
            'cron' => '* * * * *',
            'command' => 'command:does-not-exist',
            'args' => [],
        ]);

        $this->artisan('hms:cron-run', ['job' => 'bad-job'])
            ->assertExitCode(1);

        $this->assertDatabaseHas('cron_job_runs', [
            'job_key' => 'bad-job',
            'status' => 'failed',
        ]);

        Event::assertDispatched(CronJobFailed::class);
    }
}

