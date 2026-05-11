<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListCronJobs extends Command
{
    protected $signature = 'hms:cron-list {--cron-lines : Print copy/paste cPanel cron lines}';

    protected $description = 'List all configured cron jobs and their schedules';

    public function handle(): int
    {
        $jobs = config('cron.jobs', []);

        if (!is_array($jobs) || $jobs === []) {
            $this->line('No cron jobs configured.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($jobs as $key => $job) {
            $rows[] = [
                'key' => $key,
                'enabled' => (($job['enabled'] ?? true) === true) ? 'yes' : 'no',
                'cron' => (string) ($job['cron'] ?? ''),
                'command' => (string) ($job['command'] ?? ''),
                'description' => (string) ($job['description'] ?? ''),
            ];
        }

        $this->table(['key', 'enabled', 'cron', 'command', 'description'], $rows);

        if ($this->option('cron-lines')) {
            $this->line('');
            $this->line('cPanel cron lines (adjust PHP path and project path):');
            foreach ($jobs as $key => $job) {
                if (($job['enabled'] ?? true) !== true) {
                    continue;
                }
                $cron = (string) ($job['cron'] ?? '');
                $this->line($cron . ' cd /path/to/project && php artisan hms:cron-run ' . $key . ' >/dev/null 2>&1');
            }
        }

        return self::SUCCESS;
    }
}
