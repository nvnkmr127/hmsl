<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sync\Services\SyncEngine;
use App\Sync\Models\SyncOutbox;

class SyncDaemon extends Command
{
    protected $signature = 'sync:daemon';
    protected $description = 'Background sync daemon';

    protected bool $shouldStop = false;

    public function handle(SyncEngine $engine)
    {
        $this->info('Starting Sync Daemon...');
        $pid = getmypid();
        $pidPath = storage_path('app/sync_daemon.pid');
        
        $dir = dirname($pidPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($pidPath, $pid);

        $this->logInfo("Sync Daemon started with PID $pid");

        // Handle signals if supported (Unix environments)
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGterm, function () {
                $this->shouldStop = true;
                $this->logInfo("SIGTERM received, stopping cleanly...");
            });
            pcntl_signal(SIGINT, function () {
                $this->shouldStop = true;
                $this->logInfo("SIGINT received, stopping cleanly...");
            });
        }

        while (!$this->shouldStop) {
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            if ($this->shouldStop) {
                break;
            }

            if (!config('sync.enabled')) {
                $this->logInfo('Sync is disabled in config. Skipping...');
                sleep(30);
                continue;
            }

            try {
                $isReachable = $engine->isServerReachable();
                
                if ($isReachable) {
                    $pendingCount = SyncOutbox::whereIn('status', ['pending', 'failed'])
                        ->where('retry_count', '<', 3)
                        ->count();

                    $this->logInfo("Server reachable. Running performSync (pending outbox: $pendingCount)...");
                    $success = $engine->performSync();
                    $this->logInfo("Sync performed. Result: " . ($success ? "Success" : "Failed"));
                } else {
                    $pendingCount = SyncOutbox::whereIn('status', ['pending', 'failed'])
                        ->where('retry_count', '<', 3)
                        ->count();
                    $lastSync = cache('last_sync_at') ?: null;
                    $engine->writeSyncStatus('offline', $lastSync, $pendingCount);
                }
            } catch (\Exception $e) {
                $this->logInfo("Error in daemon loop: " . $e->getMessage());
            }

            sleep(30);
        }

        if (file_exists($pidPath)) {
            @unlink($pidPath);
        }
        $this->logInfo("Sync Daemon stopped.");
    }

    protected function logInfo($message)
    {
        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logPath = $logDir . '/sync_daemon-' . date('Y-m-d') . '.log';
        file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
