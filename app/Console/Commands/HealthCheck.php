<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HealthCheck extends Command
{
    protected $signature = 'app:health';
    protected $description = 'Perform application health checks and return JSON';

    public function handle()
    {
        $dbConnected = false;
        $dbError = null;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        $storageWritable = is_writable(storage_path());

        $pendingChanges = 0;
        if ($dbConnected) {
            try {
                if (class_exists(\App\Sync\Models\SyncOutbox::class)) {
                    $pendingChanges = \App\Sync\Models\SyncOutbox::where('status', 'pending')->count();
                } else if (\Illuminate\Support\Facades\Schema::hasTable('sync_outboxes')) {
                    $pendingChanges = DB::table('sync_outboxes')->where('status', 'pending')->count();
                } else if (\Illuminate\Support\Facades\Schema::hasTable('sync_outbox')) {
                    $pendingChanges = DB::table('sync_outbox')->where('status', 'pending')->count();
                }
            } catch (\Exception $e) {
                // Ignore query failures
            }
        }

        $lastSync = cache('last_sync_at') ?: null;

        $health = [
            'database' => [
                'connected' => $dbConnected,
                'error' => $dbError,
            ],
            'storage' => [
                'writable' => $storageWritable,
            ],
            'sync' => [
                'pending_changes' => $pendingChanges,
                'last_sync' => $lastSync,
            ]
        ];

        // Print raw JSON to stdout without extra newlines or output decorations
        $this->output->write(json_encode($health));
        return 0;
    }
}
