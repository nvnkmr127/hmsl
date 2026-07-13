<?php

namespace App\Sync\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Sync\Models\SyncDevice;

class ValidateSyncDevice
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 1. Check that the authenticated model is a SyncDevice
        if (!$user || !($user instanceof SyncDevice)) {
            return response()->json([
                'error' => 'Unauthorized. Sanctum token must belong to a sync device.'
            ], 403);
        }

        // 2. Check device is not 'suspended'
        if ($user->status === 'suspended') {
            return response()->json([
                'error' => 'Device is suspended.',
                'suspended_at' => $user->suspended_at ? $user->suspended_at->toIso8601String() : null,
                'suspended_reason' => $user->suspended_reason,
            ], 403);
        }

        // 3. Add $request->syncDevice for use in controllers
        $request->merge(['syncDevice' => $user]);

        return $next($request);
    }
}
