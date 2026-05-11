<?php

namespace App\Console\Commands;

use App\Events\System\CronJobFailed;
use App\Models\CronJobRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class RunConfiguredCronJob extends Command
{
    protected $signature = 'hms:cron-run {job : Job key from config/cron.php}';

    protected $description = 'Run a configured cron job with centralized logging and failure alerting';

    public function handle(): int
    {
        $jobKey = (string) $this->argument('job');
        $job = config("cron.jobs.{$jobKey}");

        if (!$job || !is_array($job)) {
            $this->error("Unknown cron job key: {$jobKey}");
            return 2;
        }

        if (($job['enabled'] ?? true) !== true) {
            return self::SUCCESS;
        }

        $command = (string) ($job['command'] ?? '');
        $args = is_array($job['args'] ?? null) ? $job['args'] : [];

        if ($command === '') {
            $this->error("Cron job {$jobKey} is missing a command.");
            return 2;
        }

        $logPath = (string) ($job['log_path'] ?? storage_path("logs/cron/{$jobKey}.log"));
        $logDir = dirname($logPath);
        if (!File::isDirectory($logDir)) {
            File::makeDirectory($logDir, 0755, true);
        }

        $run = CronJobRun::create([
            'job_key' => $jobKey,
            'command' => $this->formatCommandWithArgs($command, $args),
            'status' => 'running',
            'output_path' => $logPath,
            'host' => gethostname() ?: null,
            'started_at' => now(),
        ]);

        $output = new BufferedOutput();
        $exitCode = 0;
        $error = null;

        try {
            $exitCode = (int) Artisan::call($command, $args, $output);
        } catch (Throwable $e) {
            $exitCode = 1;
            $error = $e;
        }

        $out = $output->fetch();
        $finishedAt = now();
        $status = $exitCode === 0 ? 'succeeded' : 'failed';

        $errorMessage = null;
        if ($error) {
            $errorMessage = Str::limit($error::class . ': ' . $error->getMessage(), 2000, '');
        }

        $run->update([
            'status' => $status,
            'exit_code' => $exitCode,
            'error_message' => $errorMessage,
            'finished_at' => $finishedAt,
        ]);

        $this->appendLog($logPath, $run->id, $jobKey, $command, $args, $exitCode, $error, $out, $finishedAt);

        if ($exitCode !== 0) {
            try {
                event(new CronJobFailed([
                    'job_key' => $jobKey,
                    'run_id' => $run->id,
                    'command' => $command,
                    'args' => $args,
                    'exit_code' => $exitCode,
                    'error' => $errorMessage,
                    'started_at' => $run->started_at?->toIso8601String(),
                    'finished_at' => $finishedAt->toIso8601String(),
                    'output_path' => $logPath,
                    'host' => $run->host,
                ]));
            } catch (Throwable $e) {
                @file_put_contents(
                    $logPath,
                    "alert_dispatch_failed=" . $e::class . ': ' . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function appendLog(
        string $path,
        int $runId,
        string $jobKey,
        string $command,
        array $args,
        int $exitCode,
        ?Throwable $error,
        string $output,
        $finishedAt
    ): void {
        $lines = [];
        $lines[] = str_repeat('=', 80);
        $lines[] = 'timestamp=' . now()->toIso8601String();
        $lines[] = "run_id={$runId}";
        $lines[] = "job_key={$jobKey}";
        $lines[] = 'command=' . $this->formatCommandWithArgs($command, $args);
        $lines[] = "exit_code={$exitCode}";
        $lines[] = 'finished_at=' . $finishedAt->toIso8601String();

        if ($error) {
            $lines[] = 'error=' . $error::class . ': ' . $error->getMessage();
        }

        if (trim($output) !== '') {
            $lines[] = '';
            $lines[] = trim($output);
        }

        $lines[] = '';

        @file_put_contents($path, implode("\n", $lines) . "\n", FILE_APPEND);
    }

    private function formatCommandWithArgs(string $command, array $args): string
    {
        $parts = [$command];

        foreach ($args as $key => $value) {
            if (is_int($key)) {
                $parts[] = (string) $value;
                continue;
            }

            if (is_bool($value)) {
                if ($value) {
                    $parts[] = (string) $key;
                }
                continue;
            }

            if (is_array($value)) {
                $parts[] = (string) $key . '=' . json_encode($value);
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $parts[] = (string) $key . '=' . (string) $value;
        }

        return implode(' ', $parts);
    }
}
