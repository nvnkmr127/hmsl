<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'patients', 'doctors', 'appointments', 'bills', 'bill_items',
    'medicines', 'prescriptions', 'lab_orders', 'admissions',
    'beds', 'wards', 'departments', 'users', 'services'
];

foreach ($tables as $table) {
    try {
        $count = Illuminate\Support\Facades\DB::table($table)->count();
        echo "$table: $count\n";
    } catch (\Exception $e) {
        echo "$table: error (" . $e->getMessage() . ")\n";
    }
}
