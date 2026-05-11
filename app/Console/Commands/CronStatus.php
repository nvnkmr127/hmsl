<?php

namespace App\Console\Commands;

use App\Models\CronJobRun;
use Illuminate\Console\Command;

class CronStatus extends Command
{
    protected $signature = 'hms:cron-status {--hours=48 : Lookback window for failures}';

    protected $description = 'Show recent cron job runs and exit non-zero if failures are found';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $hours = 48;
        }

        $jobs = config('cron.jobs', []);
        $rows = [];

        foreach ($jobs as $key => $job) {
            $last = CronJobRun::where('job_key', $key)->orderByDesc('id')->first();
            $rows[] = [
                'key' => $key,
                'enabled' => (($job['enabled'] ?? true) === true) ? 'yes' : 'no',
                'last_status' => $last?->status ?? '-',
                'exit' => $last?->exit_code !== null ? (string) $last->exit_code : '-',
                'finished_at' => $last?->finished_at?->toDateTimeString() ?? '-',
            ];
        }

        $this->table(['key', 'enabled', 'last_status', 'exit', 'finished_at'], $rows);

        $failedCount = CronJobRun::where('status', 'failed')
            ->where('started_at', '>=', now()->subHours($hours))
            ->count();

        return $failedCount > 0 ? 1 : 0;
    }
}
