<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientVaccination;
use App\Models\PatientVital;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\Ward;
use App\Services\PatientService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ComprehensiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $patientService = new PatientService();
        $doctor = Doctor::first();
        $admin = User::first();
        $services = \App\Models\Service::all();
        $medicines = Medicine::all();
        $labTests = LabTest::all();
        $vaccines = Vaccine::all();
        $beds = Bed::where('is_available', true)->get();

        // 1. Create 20 Patients (all children for Dwarakamai Children's Hospital)
        for ($i = 0; $i < 20; $i++) {
            $gender = $faker->randomElement(['Male', 'Female']);
            $firstName = $faker->firstName($gender == 'Male' ? 'male' : 'female');
            $lastName = $faker->lastName;
            
            $patient = Patient::create([
                'uhid' => $patientService->generateUHID(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'father_name' => $faker->name('male'),
                'mother_name' => $faker->name('female'),
                'gender' => $gender,
                'date_of_birth' => $faker->dateTimeBetween('-12 years', '-1 months')->format('Y-m-d'),
                'phone' => '9' . $faker->numerify('#########'),
                'email' => $faker->unique()->safeEmail,
                'blood_group' => $faker->randomElement(['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-']),
                'address' => $faker->streetAddress,
                'city' => 'Nizamabad',
                'state' => 'Telangana',
                'pincode' => '50300' . rand(1, 5),
            ]);

            // 2. Add Vital Logs for each patient (2-5 logs per patient)
            for ($j = 0; $j < rand(2, 5); $j++) {
                PatientVital::create([
                    'patient_id' => $patient->id,
                    'weight' => rand(3, 40) + ($faker->randomFloat(1, 0, 9) / 10),
                    'height' => rand(50, 150),
                    'temperature' => $faker->randomFloat(1, 97, 103),
                    'pulse' => rand(70, 120),
                    'resp_rate' => rand(18, 30),
                    'spo2' => rand(95, 100),
                    'recorded_by' => $admin->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 60)),
                ]);
            }

            // 3. Outpatient Visits (Consultations) - 1 to 3 per patient
            for ($k = 0; $k < rand(1, 3); $k++) {
                $visitDate = Carbon::now()->subDays(rand(0, 30));
                $consultant = Consultation::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'service_id' => $services->where('category', 'OPD')->random()->id,
                    'token_number' => rand(1, 50),
                    'consultation_date' => $visitDate,
                    'valid_upto' => $visitDate->copy()->addDays(7),
                    'fee' => 500,
                    'status' => 'Completed',
                    'payment_status' => 'Paid',
                    'payment_method' => $faker->randomElement(['Cash', 'UPI', 'Card']),
                ]);

                // 4. Bills for Consultations
                $billNumber = 'INV-' . $visitDate->format('Ymd') . '-' . str_pad($consultant->id, 4, '0', STR_PAD_LEFT);
                $bill = Bill::create([
                    'bill_number' => $billNumber,
                    'patient_id' => $patient->id,
                    'consultation_id' => $consultant->id,
                    'subtotal' => 500,
                    'total_amount' => 500,
                    'payment_status' => 'Paid',
                    'payment_method' => $consultant->payment_method,
                    'created_by' => $admin->id,
                    'created_at' => $visitDate,
                ]);

                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => 'Consultation Fee',
                    'item_type' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'total_price' => 500,
                ]);

                // 5. Prescriptions (Medication Records)
                $prescMedicines = [];
                for ($m = 0; $m < rand(1, 3); $m++) {
                    $med = $medicines->random();
                    $prescMedicines[] = [
                        'medicine_id' => $med->id,
                        'medicine_name' => $med->name,
                        'dosage' => '1-0-1',
                        'duration' => '5 Days',
                        'instructions' => 'After food',
                    ];
                }

                Prescription::create([
                    'consultation_id' => $consultant->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'chief_complaint' => $faker->sentence,
                    'diagnosis' => $faker->word . ' infection',
                    'advice' => 'Rest and plenty of fluids.',
                    'medicines' => $prescMedicines,
                    'created_by' => $admin->id,
                    'created_at' => $visitDate,
                ]);

                // 6. Lab Orders (Diagnostic Reports)
                if (rand(0, 1)) {
                    $test = $labTests->random();
                    LabOrder::create([
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'lab_test_id' => $test->id,
                        'consultation_id' => $consultant->id,
                        'status' => 'Completed',
                        'completed_at' => $visitDate->copy()->addHours(2),
                        'results' => ['observation' => 'Normal'],
                        'notes' => 'Routine checkup',
                    ]);
                }
            }

            // 7. Vaccination Records
            $ageInMonths = Carbon::parse($patient->date_of_birth)->diffInMonths(now());
            if ($ageInMonths < 60 && $vaccines->count() > 0) { // Under 5 years
                $patientVaccines = $vaccines->random(min($vaccines->count(), rand(1, 5)));
                foreach ($patientVaccines as $v) {
                    PatientVaccination::create([
                        'patient_id' => $patient->id,
                        'vaccine_id' => $v->id,
                        'date_given' => Carbon::now()->subMonths(rand(1, 12)),
                        'administered_by' => $admin->id,
                        'batch_number' => 'BCH-' . rand(100, 999),
                    ]);
                }
            }

            // 8. Inpatient Admissions (for 7 patients)
            if ($i < 7 && $beds->count() > 0) {
                $bed = $beds->shift();
                $admissionDate = Carbon::now()->subDays(rand(2, 10));
                $isDischarged = $i < 4; // Discharge first 4
                
                $admission = Admission::create([
                    'admission_number' => 'ADM-' . $admissionDate->format('Ymd') . '-' . $patient->id,
                    'patient_id' => $patient->id,
                    'bed_id' => $bed->id,
                    'doctor_id' => $doctor->id,
                    'admission_date' => $admissionDate,
                    'discharge_date' => $isDischarged ? Carbon::now()->subDay() : null,
                    'reason_for_admission' => $faker->randomElement(['Severe Fever', 'Dehydration', 'Respiratory Distress', 'Observation']),
                    'status' => $isDischarged ? 'Discharged' : 'Admitted',
                    'created_by' => $admin->id,
                ]);

                $bed->update(['is_available' => false]);

                // Admit vitals
                PatientVital::create([
                    'patient_id' => $patient->id,
                    'admission_id' => $admission->id,
                    'weight' => rand(5, 30),
                    'temperature' => 101.5,
                    'recorded_by' => $admin->id,
                    'created_at' => $admissionDate,
                ]);

                if ($isDischarged) {
                    $bed->update(['is_available' => true]);
                }
            }
        }
    }
}
