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
    public function processIncomingChanges(array $changes, string $deviceId = null): array
    {
        $results = ['processed' => [], 'conflicts' => []];

        DB::transaction(function () use ($changes, $deviceId, &$results) {
            foreach ($changes as $change) {
                $table = $change['table_name'] ?? $change['table'] ?? null;
                $action = $change['action'];
                $data = $change['payload'] ?? $change['data'] ?? null;
                
                if (!$table || !$data) continue;

                $data = $this->sanitizeData($data);

                $uuid = $data['sync_id'] ?? null;
                if (!$uuid) continue;

                $existing = DB::table($table)->where('sync_id', $uuid)->first();

                if (!$existing && isset($data['id'])) {
                    $existingById = DB::table($table)->where('id', $data['id'])->first();
                    if ($existingById) {
                        DB::table($table)->where('id', $data['id'])->update(['sync_id' => $uuid]);
                        $existing = DB::table($table)->where('sync_id', $uuid)->first();
                    }
                }

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
                    'device_id' => $deviceId ?? '00000000-0000-0000-0000-000000000000',
                    'action' => 'push_record',
                    'details' => [
                        'table' => $table,
                        'record_uuid' => $uuid,
                        'action' => $action,
                        'status' => in_array($uuid, $results['conflicts']) ? 'conflict' : 'success',
                    ],
                    'ip_address' => request()->ip(),
                ]);
            }
        });

        return $results;
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
                        $data[$key] = Carbon::parse($value)->format('Y-m-d H:i:s');
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

    /**
     * Resolve a sync conflict.
     */
    public function resolveConflict(int $conflictId, string $resolution): void
    {
        $conflict = \App\Sync\Models\SyncConflict::findOrFail($conflictId);

        if ($resolution === 'local') {
            $conflict->update([
                'resolution' => 'resolved_local',
                'resolved_at' => now(),
            ]);
        } elseif ($resolution === 'server') {
            $table = $conflict->table_name;
            $serverData = $conflict->server_data;
            $uuid = $conflict->record_uuid;

            DB::transaction(function () use ($table, $uuid, $serverData) {
                $dataToUpdate = $serverData;
                unset($dataToUpdate['id']);

                $existing = DB::table($table)->where('sync_id', $uuid)->first();
                if ($existing) {
                    DB::table($table)->where('sync_id', $uuid)->update($dataToUpdate);
                } else {
                    DB::table($table)->insert($serverData);
                }
            });

            $conflict->update([
                'resolution' => 'resolved_server',
                'resolved_at' => now(),
            ]);
        }
    }
}
