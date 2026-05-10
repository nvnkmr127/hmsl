<?php

namespace Tests\Feature;

use App\Livewire\Ipd\IpdVitals;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IpdVitalsSyncToPatientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_ipd_vitals_also_adds_to_patient_vitals_log(): void
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

        $ward = Ward::create([
            'name' => 'Ward A',
            'type' => 'General',
            'daily_charge' => 1000,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $bed = Bed::create([
            'ward_id' => $ward->id,
            'bed_number' => 'A-01',
            'is_available' => false,
        ]);

        $admission = Admission::create([
            'admission_number' => 'ADM-TEST-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subHour(),
            'reason_for_admission' => 'Observation',
            'status' => 'Admitted',
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(IpdVitals::class, ['admission' => $admission])
            ->set('bp_systolic', 120)
            ->set('bp_diastolic', 80)
            ->set('pulse', 72)
            ->set('temperature', 98.6)
            ->set('spo2', 98)
            ->set('resp_rate', 18)
            ->call('save');

        $this->assertDatabaseCount('ipd_vitals', 1);
        $this->assertDatabaseHas('ipd_vitals', [
            'admission_id' => $admission->id,
            'patient_id' => $patient->id,
            'recorded_by' => $user->id,
        ]);

        $this->assertDatabaseCount('patient_vitals', 1);
        $this->assertDatabaseHas('patient_vitals', [
            'patient_id' => $patient->id,
            'admission_id' => $admission->id,
            'recorded_by' => $user->id,
        ]);
    }
}

