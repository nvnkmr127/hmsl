<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Patient;
use App\Models\Consultation;
use App\Services\OpdService;
use Carbon\Carbon;

try {
    $patientPhone = '9999999997';
    $patient = Patient::firstOrCreate(
        ['phone' => $patientPhone],
        ['first_name' => 'Test', 'last_name' => 'Newborn', 'gender' => 'Other', 'date_of_birth' => '2026-05-22', 'uhid' => 'TEST99997']
    );
    $patient->update(['date_of_birth' => '2026-05-22']);
    Consultation::where('patient_id', $patient->id)->delete();

    $dateStr = '2026-05-22';
    $date = Carbon::parse($dateStr);
    $dob = Carbon::parse($patient->date_of_birth)->startOfDay();
    $diff = $dob->diffInDays($date->copy()->startOfDay());
    
    echo "DOB: " . $dob->toDateString() . "\n";
    echo "Visit: " . $date->toDateString() . "\n";
    echo "DiffInDays: " . $diff . "\n";
    
    $isZeroDaysAge = $patient->date_of_birth && $diff === 0;
    echo "isZeroDaysAge: " . ($isZeroDaysAge ? 'true' : 'false') . "\n";

    $service = app(OpdService::class);

    echo "Test 1: Newborn visit on 22/05/2026 (age 0)\n";
    $date1 = '2026-05-22';
    $details1 = $service->calculateBookingDetails($patient, null, null, $date1);
    echo "Valid Upto: " . $details1['valid_upto'] . "\n\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($patient)) {
        Consultation::where('patient_id', $patient->id)->delete();
        $patient->delete();
    }
}
