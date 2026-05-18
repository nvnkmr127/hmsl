<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Sync\Models\SyncOutbox;
use Illuminate\Support\Facades\Http;

$pending = SyncOutbox::where('status', 'pending')->limit(50)->get();
if ($pending->isEmpty()) {
    die("No pending records to push!\n");
}

$serverUrl = config('sync.server_url');
$token = config('sync.token');

echo "Pushing " . $pending->count() . " records to " . $serverUrl . "...\n";

$mappedChanges = $pending->map(function ($item) {
    return [
        'table' => $item->table_name,
        'action' => $item->action,
        'data' => is_string($item->payload) ? json_decode($item->payload, true) : $item->payload,
        'sync_version' => $item->sync_version,
    ];
})->toArray();

$response = Http::withToken($token)
    ->post($serverUrl . '/api/v1/sync/push', [
        'device_id' => config('sync.device_id'),
        'changes' => $mappedChanges
    ]);

echo "Response Status: " . $response->status() . "\n";
echo "Response Body: " . $response->body() . "\n";

if ($response->successful()) {
    echo "SUCCESS!\n";
} else {
    echo "FAILED!\n";
}
