<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\IpdService;
use App\Models\Admission;
use App\Models\Patient;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;

echo "--- START ADMISSION TEST ---\n";

DB::beginTransaction();

try {
    // 1. Get or create a patient, doctor, ward, bed
    $patient = Patient::first() ?? Patient::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'uhid' => 'UHID-TEST-999',
        'phone' => '9999999999',
        'gender' => 'Male',
        'date_of_birth' => '1990-01-01',
    ]);

    $doctor = Doctor::first() ?? Doctor::create([
        'first_name' => 'Doctor',
        'last_name' => 'Who',
        'specialization' => 'General Medicine',
    ]);

    $ward = Ward::first() ?? Ward::create([
        'name' => 'General Ward',
        'code' => 'GW',
        'type' => 'General',
    ]);

    $bed = Bed::where('is_available', true)->first() ?? Bed::create([
        'bed_number' => 'B-TEST-999',
        'ward_id' => $ward->id,
        'is_available' => true,
    ]);

    echo "Using Bed ID: {$bed->id}, Ward: {$ward->name}\n";

    // 2. Test admitting patient with a custom manual number
    $ipdService = app(IpdService::class);
    
    $admissionNumber = 'ADM-99999';
    
    echo "Admitting patient with number: {$admissionNumber}\n";
    $admission = $ipdService->admitPatient([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'ward_id' => $ward->id,
        'bed_id' => $bed->id,
        'admission_date' => now()->toDateTimeString(),
        'admission_number' => $admissionNumber,
        'reason_for_admission' => 'Test',
    ]);

    echo "Admission successful! Saved admission number: {$admission->admission_number}\n";

    if ($admission->admission_number === $admissionNumber) {
        echo "✅ PASS: Manual admission number matches.\n";
    } else {
        echo "❌ FAIL: Manual admission number does not match.\n";
    }

    // 3. Test duplicate admission number validation (must fail)
    echo "Attempting duplicate admission with same number: {$admissionNumber}\n";
    
    // We free the bed so availability doesn't block us (or use a different bed, or test duplicate admission number check specifically)
    $bed2 = Bed::create([
        'bed_number' => 'B-TEST-998',
        'ward_id' => $ward->id,
        'is_available' => true,
    ]);

    // Create another patient to bypass the patient already admitted check
    $patient2 = Patient::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'uhid' => 'UHID-TEST-998',
        'phone' => '8888888888',
        'gender' => 'Female',
        'date_of_birth' => '1995-01-01',
    ]);

    try {
        $ipdService->admitPatient([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'ward_id' => $ward->id,
            'bed_id' => $bed2->id,
            'admission_date' => now()->toDateTimeString(),
            'admission_number' => $admissionNumber,
            'reason_for_admission' => 'Duplicate Test',
        ]);
        echo "❌ FAIL: Duplicate admission number did not trigger exception.\n";
    } catch (\RuntimeException $e) {
        if ($e->getMessage() === 'admission number exist already') {
            echo "✅ PASS: Uniqueness validation triggered exception: '{$e->getMessage()}'\n";
        } else {
            echo "❌ FAIL: Unexpected exception message: '{$e->getMessage()}'\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    // Rollback so we don't mess up local database
    DB::rollBack();
    echo "Database rolled back. Test complete.\n";
}
