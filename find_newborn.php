<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \App\Models\Consultation::whereHas('service', fn($q) => $q->where('name', 'new born'))->first();
if ($c) {
    echo "Patient: " . $c->patient->full_name . " (Phone: " . $c->patient->phone . ")\n";
    echo "Date: " . $c->consultation_date . "\n";
} else {
    echo "No newborn consultations found.\n";
}
