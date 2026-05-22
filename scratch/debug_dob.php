<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Patient;
use Carbon\Carbon;

$p = Patient::where('uhid', 'TEST99998')->first();
if ($p) {
    var_dump($p->date_of_birth);
    var_dump(Carbon::parse($p->date_of_birth)->startOfDay()->diffInDays(Carbon::parse('2026-05-22')->startOfDay()));
} else {
    echo "Patient not found\n";
}
