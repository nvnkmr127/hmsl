<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sync\Services\SyncEngine;

class SyncPerform extends Command
{
    protected $signature = 'sync:perform {--output= : Output format (e.g. json)}';
    protected $description = 'Manually trigger a data synchronization cycle';

    public function handle(SyncEngine $engine)
    {
        $isJson = $this->option('output') === 'json';

        if (!config('sync.enabled')) {
            if ($isJson) {
                $this->output->write(json_encode([
                    'success' => false,
                    'pushed' => 0,
                    'pulled' => 0,
                    'error' => 'Synchronization is currently disabled.'
                ]));
            } else {
                $this->warn('Synchronization is currently disabled.');
            }
            return 0;
        }

        if (!$isJson) {
            $this->info('Starting Synchronization...');
        }

        try {
            $success = $engine->performSync();
            $stats = $engine->lastSyncStats;

            if ($isJson) {
                $this->output->write(json_encode([
                    'success' => $success,
                    'pushed' => $stats['pushed'] ?? 0,
                    'pulled' => $stats['pulled'] ?? 0,
                    'error' => $success ? null : 'Sync failed (server unreachable or error occurred)'
                ]));
            } else {
                if ($success) {
                    $this->table(['Metric', 'Count'], [
                        ['Pushed Changes', $stats['pushed']],
                        ['Pulled Deltas', $stats['pulled']],
                    ]);
                    $this->info('Synchronization Complete!');
                } else {
                    $this->error('Synchronization Failed (returned false).');
                    return 1;
                }
            }
        } catch (\Exception $e) {
            if ($isJson) {
                $this->output->write(json_encode([
                    'success' => false,
                    'pushed' => 0,
                    'pulled' => 0,
                    'error' => $e->getMessage()
                ]));
            } else {
                $this->error('Sync Failed: ' . $e->getMessage());
            }
            return 1;
        }

        return 0;
    }
}
