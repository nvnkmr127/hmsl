<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientVital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillPatientVitalsFromConsultationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_command_creates_patient_vitals_for_old_consultations(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => null,
            'department_id' => $department->id,
            'full_name' => 'Dr Demo',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'created_by' => $user->id,
            'token_number' => 1,
            'consultation_date' => now()->toDateString(),
            'fee' => 500,
            'status' => 'Pending',
            'payment_status' => 'Paid',
            'weight' => 70,
            'height' => 170,
            'temperature' => 98.6,
        ]);

        $this->assertDatabaseCount('patient_vitals', 0);

        $this->actingAs($user)->artisan('hms:backfill-patient-vitals')->assertExitCode(0);

        $this->assertDatabaseHas('patient_vitals', [
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'recorded_by' => $user->id,
        ]);
    }

    public function test_backfill_command_is_idempotent(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => null,
            'department_id' => $department->id,
            'full_name' => 'Dr Demo',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'created_by' => $user->id,
            'token_number' => 1,
            'consultation_date' => now()->toDateString(),
            'fee' => 500,
            'status' => 'Pending',
            'payment_status' => 'Paid',
            'weight' => 70,
            'height' => 170,
            'temperature' => 98.6,
        ]);

        PatientVital::create([
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'weight' => 70,
            'height' => 170,
            'temperature' => 98.6,
            'recorded_by' => $user->id,
        ]);

        $this->assertDatabaseCount('patient_vitals', 1);

        $this->actingAs($user)->artisan('hms:backfill-patient-vitals')->assertExitCode(0);
        $this->actingAs($user)->artisan('hms:backfill-patient-vitals')->assertExitCode(0);

        $this->assertDatabaseCount('patient_vitals', 1);
        $this->assertDatabaseHas('patient_vitals', [
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
        ]);
    }
}
