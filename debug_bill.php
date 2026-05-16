<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admission;
use App\Services\IpdService;

$admission = Admission::find(1);
$items = app(IpdService::class)->buildFinalBillItems($admission);
print_r($items);
