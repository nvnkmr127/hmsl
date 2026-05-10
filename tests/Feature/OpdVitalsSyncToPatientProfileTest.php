<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\OpdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OpdVitalsSyncToPatientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_opd_with_vitals_also_creates_patient_vitals_log(): void
    {
        Event::fake();

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

        $service = Service::create([
            'name' => 'OPD General',
            'category' => 'OPD',
            'department_id' => $department->id,
            'price' => 500,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $consultation = app(OpdService::class)->bookAppointment([
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'visit_type' => 'New',
            'weight' => 70,
            'height' => 170,
            'temperature' => 98.6,
            'fee' => 500,
            'consultation_date' => now()->toDateString(),
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'notes' => null,
        ]);

        $this->assertDatabaseHas('patient_vitals', [
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'recorded_by' => $user->id,
        ]);
    }
}

