<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sync\Services\SyncEngine;
use Illuminate\Support\Facades\Log;

class SyncMonitor extends Command
{
    protected $signature = 'sync:monitor';
    protected $description = 'Monitor connection and auto-trigger synchronization';

    public function handle(SyncEngine $engine)
    {
        $this->info('Starting Sync Monitor...');
        
        $wasOnline = null; // Uninitialized state

        while (true) {
            try {
                $isOnline = $engine->isServerReachable();
                
                $this->info(now()->toDateTimeString() . ': Server is ' . ($isOnline ? 'ONLINE' : 'OFFLINE'));

                $pendingCount = \App\Sync\Models\SyncOutbox::whereIn('status', ['pending', 'failed'])
                    ->where('retry_count', '<', 3)
                    ->count();

                $lastSync = cache('last_sync_at') ?: null;

                if ($wasOnline === false && $isOnline === true) {
                    $this->info('Server became reachable! Triggering sync:perform...');
                    try {
                        $this->call('sync:perform');
                    } catch (\Exception $e) {
                        Log::error('Sync Monitor: Failed to execute sync:perform: ' . $e->getMessage());
                    }
                } else {
                    // Update sync_status.json based on connection reachability
                    if (!$isOnline) {
                        $engine->writeSyncStatus('offline', $lastSync, $pendingCount);
                    } else {
                        // Preserving synced/error state if online, or writing online
                        $status = 'online';
                        $syncStatusPath = storage_path('app/sync_status.json');
                        if (file_exists($syncStatusPath)) {
                            $existing = json_decode(file_get_contents($syncStatusPath), true);
                            if ($existing && isset($existing['status']) && in_array($existing['status'], ['synced', 'error'])) {
                                $status = $existing['status'];
                            }
                        }
                        $engine->writeSyncStatus($status, $lastSync, $pendingCount);
                    }
                }

                $wasOnline = $isOnline;
            } catch (\Exception $e) {
                Log::error('Sync Monitor Loop Error: ' . $e->getMessage());
            }

            sleep(60);
        }
    }
}
