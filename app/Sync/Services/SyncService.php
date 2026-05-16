<?php

namespace App\Sync\Services;

use Illuminate\Support\Facades\DB;
use App\Sync\Models\SyncAuditLog;
use Carbon\Carbon;

class SyncService
{
    /**
     * Process incoming changes from a local device.
     */
    public function processIncomingChanges(array $changes): array
    {
        $results = ['processed' => [], 'conflicts' => []];

        DB::transaction(function () use ($changes, &$results) {
            foreach ($changes as $change) {
                $table = $change['table_name'] ?? $change['table'] ?? null;
                $action = $change['action'];
                $data = $change['payload'] ?? $change['data'] ?? null;
                
                if (!$table || !$data) continue;

                $uuid = $data['sync_id'] ?? null;
                if (!$uuid) continue;

                $existing = DB::table($table)->where('sync_id', $uuid)->first();

                if ($action === 'create' || $action === 'update') {
                    if ($existing) {
                        // Conflict check: Only update if incoming version is higher
                        if (isset($data['sync_version']) && $data['sync_version'] > ($existing->sync_version ?? 0)) {
                            DB::table($table)->where('sync_id', $uuid)->update($data);
                            $results['processed'][] = $uuid;
                        } else {
                            $results['conflicts'][] = $uuid;
                        }
                    } else {
                        // Insert new record
                        DB::table($table)->insert($data);
                        $results['processed'][] = $uuid;
                    }
                } elseif ($action === 'delete') {
                    DB::table($table)->where('sync_id', $uuid)->delete();
                    $results['processed'][] = $uuid;
                }
                
                SyncAuditLog::create([
                    'table_name' => $table,
                    'record_uuid' => $uuid,
                    'action' => $action,
                    'status' => in_array($uuid, $results['conflicts']) ? 'conflict' : 'success',
                ]);
            }
        });

        return $results;
    }

    /**
     * Get changes that occurred since the last sync.
     */
    public function getDeltas(?string $lastSyncAt): array
    {
        $deltas = [];
        $tables = config('sync.tables', []);

        foreach ($tables as $table) {
            $query = DB::table($table);
            if ($lastSyncAt) {
                try {
                    $query->where('updated_at', '>', Carbon::parse($lastSyncAt));
                } catch (\Exception $e) {
                    // Fallback if date is invalid
                }
            }
            
            $records = $query->limit(100)->get();
            foreach ($records as $record) {
                $deltas[] = [
                    'table' => $table,
                    'data' => (array)$record
                ];
            }
        }

        return $deltas;
    }
}
