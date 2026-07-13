<?php

namespace App\Sync\API\Controllers;

use App\Http\Controllers\Controller;
use App\Sync\Models\SyncDevice;
use App\Sync\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $payload = $request->validate([
            'changes' => 'required|array',
            'changes.*.table' => 'required|string',
            'changes.*.action' => 'required|in:create,update,delete',
            'changes.*.data' => 'required|array',
        ]);

        $deviceId = $request->user()?->device_uuid ?? '00000000-0000-0000-0000-000000000000';

        $results = $this->syncService->processIncomingChanges($payload['changes'], $deviceId);

        return response()->json([
            'success' => true,
            'processed' => count($results['processed']),
            'conflicts' => count($results['conflicts']),
        ]);
    }

    public function pull(Request $request)
    {
        $lastSync = $request->query('last_sync_at');
        
        $deltas = $this->syncService->getDeltas($lastSync);

        return response()->json([
            'deltas' => $deltas,
            'server_time' => now(),
        ]);
    }
}
