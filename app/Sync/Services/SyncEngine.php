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
    public function performSync(): array
    {
        $stats = ['pushed' => 0, 'pulled' => 0, 'conflicts' => 0];

        // 1. Push local changes
        $stats['pushed'] = $this->pushLocalChanges();

        // 2. Pull server changes
        $stats['pulled'] = $this->pullServerChanges();

        return $stats;
    }

    protected function pushLocalChanges(): int
    {
        $pending = SyncOutbox::where('status', 'pending')->limit(50)->get();
        if ($pending->isEmpty()) return 0;

        $serverUrl = config('sync.server_url');
        $token = config('sync.token');

        if (!$serverUrl || !$token) {
            throw new \Exception('Sync configuration missing (SYNC_SERVER_URL or SYNC_TOKEN).');
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->connectTimeout(15)
                ->post($serverUrl . '/api/v1/sync/push', [
                    'device_id' => config('sync.device_id'),
                    'changes' => $pending->toArray()
                ]);

            if ($response->successful()) {
                foreach ($pending as $item) {
                    $item->update(['status' => 'completed']);
                }
                return $pending->count();
            } else {
                Log::warning('Sync Push Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Sync Push Connection Error: ' . $e->getMessage());
        }

        return 0;
    }

    protected function pullServerChanges(): int
    {
        $lastSync = cache('last_sync_at');
        
        $serverUrl = config('sync.server_url');
        $token = config('sync.token');

        if (!$serverUrl || !$token) {
            return 0;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->connectTimeout(15)
                ->get($serverUrl . '/api/v1/sync/pull', [
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
            }
        } catch (\Exception $e) {
            Log::error('Sync Pull Connection Error: ' . $e->getMessage());
        }

        return 0;
    }

    protected function applyDelta(array $delta): void
    {
        $table = $delta['table'];
        $data = $delta['data'];
        $uuid = $data['sync_id'];

        $existing = DB::table($table)->where('sync_id', $uuid)->first();

        if ($existing) {
            if ($data['sync_version'] > $existing->sync_version) {
                DB::table($table)->where('sync_id', $uuid)->update($data);
            } else if ($data['sync_version'] == $existing->sync_version && $data != (array)$existing) {
                // Potential conflict
                SyncConflict::create([
                    'table_name' => $table,
                    'record_uuid' => $uuid,
                    'local_data' => (array)$existing,
                    'server_data' => $data,
                ]);
            }
        } else {
            DB::table($table)->insert($data);
        }
    }
}
