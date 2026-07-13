<?php

namespace App\Sync\Services;

use App\Sync\Models\SyncOutbox;
use App\Sync\Models\SyncConflict;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEngine
{
    /**
     * Stash of detailed stats from the last run sync cycle.
     */
    public array $lastSyncStats = [
        'pushed' => 0,
        'pulled' => 0,
        'conflicts' => 0,
    ];

    /**
     * Check if the synchronization server is reachable.
     */
    public function isServerReachable(): bool
    {
        $serverUrl = config('sync.server_url');
        if (!$serverUrl) {
            return false;
        }

        $cleanUrl = rtrim($serverUrl, '/');
        $pingUrl = $cleanUrl . '/login';

        try {
            $response = Http::withoutVerifying()
                ->timeout(5)
                ->connectTimeout(5)
                ->get($pingUrl);

            return $response->status() < 500;
        } catch (\Exception $e) {
            Log::warning('Sync Server Reachability Check Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Write sync status metadata to sync_status.json
     */
    public function writeSyncStatus(string $status, ?string $lastSync, int $pendingChanges, ?string $error = null): void
    {
        $syncStatusPath = storage_path('app/sync_status.json');
        $dir = dirname($syncStatusPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'status' => $status,
            'last_sync' => $lastSync,
            'pending_changes' => $pendingChanges,
            'device_id' => config('sync.device_id'),
        ];

        if ($error !== null) {
            $data['error'] = $error;
        }

        file_put_contents($syncStatusPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Perform a complete push and pull sync cycle.
     */
    public function performSync(): bool
    {
        // Check if sync is enabled
        if (!config('sync.enabled')) {
            $this->writeSyncStatus('disabled', cache('last_sync_at'), 0);
            return false;
        }

        // Check if server is reachable first
        if (!$this->isServerReachable()) {
            $pendingCount = SyncOutbox::whereIn('status', ['pending', 'failed'])
                ->where('retry_count', '<', 3)
                ->count();
            $this->writeSyncStatus('offline', cache('last_sync_at'), $pendingCount);
            return false;
        }

        try {
            $this->lastSyncStats = ['pushed' => 0, 'pulled' => 0, 'conflicts' => 0];
            $attemptedIds = [];

            // Disable foreign key constraints in SQLite for the sync session
            if (config('database.default') === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            // 1. Push local changes
            $this->lastSyncStats['pushed'] = $this->pushLocalChanges($attemptedIds);

            // 2. Pull server changes
            $this->lastSyncStats['pulled'] = $this->pullServerChanges();

            // Re-enable foreign key constraints in SQLite
            if (config('database.default') === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

            $pendingCount = SyncOutbox::whereIn('status', ['pending', 'failed'])
                ->where('retry_count', '<', 3)
                ->count();

            $lastSyncTime = now()->toIso8601String();
            cache(['last_sync_at' => $lastSyncTime]);

            $this->writeSyncStatus('synced', $lastSyncTime, $pendingCount);
            return true;
        } catch (\Exception $e) {
            Log::warning('Sync Cycle Failed: ' . $e->getMessage());

            // Re-enable foreign key constraints on error
            if (config('database.default') === 'sqlite') {
                try {
                    DB::statement('PRAGMA foreign_keys = ON;');
                } catch (\Exception $ex) {}
            }

            $pendingCount = SyncOutbox::whereIn('status', ['pending', 'failed'])
                ->where('retry_count', '<', 3)
                ->count();

            $this->writeSyncStatus('error', cache('last_sync_at'), $pendingCount, $e->getMessage());
            return false;
        }
    }

    /**
     * Push local changes to the server in batches of 100.
     */
    protected function pushLocalChanges(array &$attemptedIds): int
    {
        $totalPushed = 0;
        $serverUrl = config('sync.server_url');
        $token = config('sync.token');

        if (!$serverUrl || !$token) {
            Log::warning('Sync Push: Configuration missing (SYNC_SERVER_URL or SYNC_TOKEN).');
            return 0;
        }

        while (true) {
            try {
                $pending = SyncOutbox::whereIn('status', ['pending', 'failed'])
                    ->where('retry_count', '<', 3)
                    ->whereNotIn('id', $attemptedIds)
                    ->limit(100)
                    ->get();

                if ($pending->isEmpty()) {
                    break;
                }

                foreach ($pending as $item) {
                    $attemptedIds[] = $item->id;
                }

                $mappedChanges = $pending->map(function ($item) {
                    $data = is_string($item->payload) ? json_decode($item->payload, true) : $item->payload;
                    return [
                        'table' => $item->table_name,
                        'action' => $item->action,
                        'data' => $this->sanitizeData($data),
                        'sync_version' => $item->sync_version,
                    ];
                })->toArray();

                $response = Http::withoutVerifying()
                    ->withToken($token)
                    ->timeout(30)
                    ->connectTimeout(15)
                    ->post(rtrim($serverUrl, '/') . '/api/v1/sync/push', [
                        'device_id' => config('sync.device_id'),
                        'changes' => $mappedChanges
                    ]);

                if ($response->successful()) {
                    foreach ($pending as $item) {
                        $item->update([
                            'status' => 'completed',
                            'error_message' => null,
                            'last_error' => null,
                        ]);
                    }
                    $totalPushed += $pending->count();
                } else {
                    $errorMsg = 'Server response ' . $response->status() . ': ' . $response->body();
                    Log::warning('Sync Push Batch Failed: ' . $errorMsg);
                    foreach ($pending as $item) {
                        $item->update([
                            'status' => 'failed',
                            'retry_count' => $item->retry_count + 1,
                            'error_message' => substr($errorMsg, 0, 500),
                            'last_error' => substr($errorMsg, 0, 500),
                            'failed_at' => now(),
                        ]);
                    }
                    // Break out of the loop on push failure to prevent spamming the server
                    break;
                }
            } catch (\Exception $e) {
                Log::warning('Sync Push Connection Exception: ' . $e->getMessage());
                if (isset($pending) && !$pending->isEmpty()) {
                    foreach ($pending as $item) {
                        $item->update([
                            'status' => 'failed',
                            'retry_count' => $item->retry_count + 1,
                            'error_message' => substr($e->getMessage(), 0, 500),
                            'last_error' => substr($e->getMessage(), 0, 500),
                            'failed_at' => now(),
                        ]);
                    }
                }
                // Break on connection exception
                break;
            }
        }

        return $totalPushed;
    }

    /**
     * Pull updates from the server.
     */
    protected function pullServerChanges(): int
    {
        $lastSync = cache('last_sync_at');
        $serverUrl = config('sync.server_url');
        $token = config('sync.token');

        if (!$serverUrl || !$token) {
            Log::warning('Sync Pull: Configuration missing (SYNC_SERVER_URL or SYNC_TOKEN).');
            return 0;
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->timeout(30)
                ->connectTimeout(15)
                ->get(rtrim($serverUrl, '/') . '/api/v1/sync/pull', [
                    'last_sync_at' => $lastSync
                ]);

            if ($response->successful()) {
                $deltas = $response->json('deltas') ?? [];
                $processed = 0;
                foreach ($deltas as $delta) {
                    try {
                        $this->applyDelta($delta);
                        $processed++;
                    } catch (\Exception $e) {
                        Log::error("Failed to apply delta for table {$delta['table']}: " . $e->getMessage());
                    }
                }

                cache(['last_sync_at' => $response->json('server_time')]);

                return $processed;
            } else {
                Log::warning('Sync Pull Failed: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Sync Pull Connection Exception: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Apply delta change locally.
     */
    protected function applyDelta(array $delta): void
    {
        $table = $delta['table'];
        $data = $this->sanitizeData($delta['data']);
        $uuid = $data['sync_id'] ?? null;

        // If the record from the server doesn't have a sync_id, generate one locally
        if (empty($uuid)) {
            $uuid = (string) \Illuminate\Support\Str::uuid();
            $data['sync_id'] = $uuid;
        }

        $existing = DB::table($table)->where('sync_id', $uuid)->first();

        if (!$existing && isset($data['id'])) {
            $existingById = DB::table($table)->where('id', $data['id'])->first();
            if ($existingById) {
                DB::table($table)->where('id', $data['id'])->update(['sync_id' => $uuid]);
                $existing = DB::table($table)->where('sync_id', $uuid)->first();
            }
        }

        if ($existing) {
            // Only update if the incoming version is higher
            $incomingVersion = $data['sync_version'] ?? 1;
            $existingVersion = $existing->sync_version ?? 0;
            if ($incomingVersion > $existingVersion) {
                DB::table($table)->where('sync_id', $uuid)->update($data);
            }
        } else {
            DB::table($table)->insert($data);
        }
    }

    /**
     * Sanitize and convert ISO 8601 datetime strings to database format.
     */
    protected function sanitizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
                    try {
                        $data[$key] = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        // Keep original if parsing fails
                    }
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
            }
        }
        return $data;
    }
}
