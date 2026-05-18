<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Patient;
use App\Sync\Models\SyncOutbox;

echo "Testing Observer...\n";

$p = Patient::create([
    'first_name' => 'Sync',
    'last_name' => 'Test',
    'phone' => '9998887776',
    'gender' => 'Male',
    'uhid' => 'TEST-' . time(),
    'date_of_birth' => '1990-01-01'
]);

echo "Created Patient ID: " . $p->id . "\n";
echo "Sync ID: " . $p->sync_id . "\n";

$outbox = SyncOutbox::where('record_uuid', $p->sync_id)->first();

if ($outbox) {
    echo "Outbox Record Found! Action: " . $outbox->action . "\n";
} else {
    echo "ERROR: No Outbox Record found for this patient!\n";
}

$p->delete();
