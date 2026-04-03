<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\OpdService;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = new OpdService();
        $doctor = Doctor::first();
        $patient = Patient::first();

        if ($doctor && $patient) {
            $manager->bookAppointment([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'consultation_date' => date('Y-m-d'),
                'fee' => $doctor->consultation_fee,
                'notes' => 'Fever and body pain since 2 days.',
                'status' => 'Pending'
            ]);

            $manager->bookAppointment([
                'patient_id' => Patient::skip(1)->first()->id,
                'doctor_id' => $doctor->id,
                'consultation_date' => date('Y-m-d'),
                'fee' => $doctor->consultation_fee,
                'notes' => 'Regular checkup.',
                'status' => 'Ongoing'
            ]);
        }
    }
}
