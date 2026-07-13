<?php

namespace App\Sync\API\Controllers;

use App\Http\Controllers\Controller;
use App\Sync\Models\SyncDevice;
use App\Sync\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncController extends Controller
{
    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function registerDevice(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|uuid',
            'name' => 'required|string',
        ]);

        $device = SyncDevice::firstOrCreate(
            ['device_uuid' => $request->device_uuid],
            ['name' => $request->name, 'status' => 'active']
        );

        $token = $device->createToken('sync-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'device_id' => $device->id,
        ]);
    }

    public function push(Request $request)
    {
        // 1. Validate max 100 records per push
        $changes = $request->input('changes', []);
        if (!is_array($changes) || count($changes) > 100) {
            return response()->json([
                'message' => 'The changes array must not contain more than 100 items.',
                'errors' => [
                    'changes' => ['Maximum 100 records per push allowed.']
                ]
            ], 422);
        }

        // 2. Validate table_name is in sync whitelist and action is valid
        $allowedTables = config('sync.tables', []);
        foreach ($changes as $index => $change) {
            $table = $change['table'] ?? $change['table_name'] ?? null;
            if (!$table || !in_array($table, $allowedTables)) {
                return response()->json([
                    'message' => "Validation failed: Table '$table' is not whitelisted for synchronization.",
                    'errors' => [
                        "changes.{$index}.table" => ["Table '$table' is not whitelisted."]
                    ]
                ], 422);
            }

            $action = $change['action'] ?? null;
            if (!in_array($action, ['create', 'update', 'delete'])) {
                return response()->json([
                    'message' => "Validation failed: Action '$action' is invalid.",
                    'errors' => [
                        "changes.{$index}.action" => ["Action must be one of: create, update, delete."]
                    ]
                ], 422);
            }
        }

        // 3. Resolve device from middleware
        $device = $request->syncDevice;
        $deviceId = $device ? $device->device_uuid : '00000000-0000-0000-0000-000000000000';

        // 4. Wrap entire push in a DB transaction
        DB::beginTransaction();
        try {
            $results = $this->syncService->processIncomingChanges($changes, $deviceId);
            DB::commit();

            // Update last_sync_at timestamp for device
            if ($device) {
                $device->update(['last_sync_at' => now()]);
            }

            return response()->json([
                'accepted' => count($results['processed']),
                'skipped' => count($results['conflicts']),
                'conflicts' => $results['conflicts'],
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'accepted' => 0,
                'skipped' => count($changes),
                'conflicts' => [],
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function pull(Request $request)
    {
        // 1. Require since parameter
        $since = $request->query('since');
        if ($since === null) {
            return response()->json([
                'message' => 'The since parameter is required.',
                'errors' => [
                    'since' => ['The since parameter is required (use 0 for full sync).']
                ]
            ], 422);
        }

        $sinceTime = ($since === '0' || $since == 0) ? null : $since;

        // 2. Fetch all matching records across whitelisted tables
        $allRecords = [];
        $tables = config('sync.tables', []);

        foreach ($tables as $table) {
            $query = DB::table($table);
            if ($sinceTime) {
                try {
                    $query->where('updated_at', '>', Carbon::parse($sinceTime));
                } catch (\Exception $e) {
                    // Fallback on parsing error
                }
            }

            $records = $query->get()->map(function ($record) use ($table) {
                return [
                    'table' => $table,
                    'updated_at' => $record->updated_at,
                    'data' => (array)$record,
                ];
            });

            foreach ($records as $r) {
                $allRecords[] = $r;
            }
        }

        // 3. Sort chronologically by updated_at
        usort($allRecords, function ($a, $b) {
            return strcmp($a['updated_at'], $b['updated_at']);
        });

        // 4. Filter out records originally created/pushed by the pulling device
        $device = $request->syncDevice;
        $deviceId = $device ? $device->device_uuid : '00000000-0000-0000-0000-000000000000';

        $pushedRecordUuids = DB::table('sync_audit_log')
            ->where('device_id', $deviceId)
            ->where('action', 'push_record')
            ->get()
            ->map(function ($log) {
                $details = is_string($log->details) ? json_decode($log->details, true) : (array)$log->details;
                if (isset($details['action']) && $details['action'] === 'create') {
                    return $details['record_uuid'] ?? null;
                }
                return null;
            })
            ->filter()
            ->unique()
            ->toArray();

        $filteredRecords = [];
        foreach ($allRecords as $record) {
            $syncId = $record['data']['sync_id'] ?? null;
            if ($syncId && in_array($syncId, $pushedRecordUuids)) {
                continue;
            }
            $filteredRecords[] = $record;
        }

        // 5. Paginate max 500 records
        $total = count($filteredRecords);
        $chunk = array_slice($filteredRecords, 0, 500);
        $hasMore = $total > 500;

        $nextCursor = null;
        if (count($chunk) > 0) {
            $lastItem = end($chunk);
            $nextCursor = Carbon::parse($lastItem['updated_at'])->toIso8601String();
        }

        $formattedData = array_map(function ($item) {
            return [
                'table' => $item['table'],
                'data' => $item['data'],
            ];
        }, $chunk);

        return response()->json([
            'data' => $formattedData,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ]);
    }

    public function status(Request $request)
    {
        $device = $request->syncDevice;

        $totalOutboxPending = DB::table('sync_outbox')
            ->where('device_id', $device->device_uuid)
            ->whereIn('status', ['pending', 'failed'])
            ->count();

        $conflictCount = DB::table('sync_conflicts')
            ->where('resolution', 'pending')
            ->count();

        return response()->json([
            'device' => [
                'id' => $device->id,
                'device_uuid' => $device->device_uuid,
                'name' => $device->name,
                'os_version' => $device->os_version,
                'status' => $device->status,
                'created_at' => $device->created_at->toIso8601String(),
                'updated_at' => $device->updated_at->toIso8601String(),
            ],
            'last_sync_at' => $device->last_sync_at ? $device->last_sync_at->toIso8601String() : null,
            'total_outbox_pending' => $totalOutboxPending,
            'conflict_count' => $conflictCount,
        ]);
    }
}
