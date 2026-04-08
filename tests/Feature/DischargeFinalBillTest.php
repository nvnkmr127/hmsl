<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DischargeFinalBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_final_bill_for_already_discharged_admission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('doctor_owner');

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalOwner::setOwnerDoctor($doctor);

        $patient = Patient::create([
            'uhid' => 'UHID-IPD-BILL-0001',
            'first_name' => 'IPD',
            'last_name' => 'Patient',
            'gender' => 'female',
            'date_of_birth' => '1990-01-01',
            'phone' => '9888877777',
            'is_active' => true,
        ]);

        $ward = Ward::create([
            'name' => 'Ward A',
            'type' => 'General',
            'daily_charge' => 1000,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'A-01', 'is_available' => true]);

        $admission = Admission::create([
            'admission_number' => 'ADM-FINAL-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDay(),
            'discharge_date' => now(),
            'reason_for_admission' => 'Observation',
            'status' => 'Discharged',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('discharge.summary', ['admission' => $admission->id]))
            ->assertOk()
            ->assertSee('Final bill not generated.');

        $this->actingAs($user)
            ->post(route('discharge.final-bill', ['admission' => $admission->id]))
            ->assertRedirect(route('discharge.summary', ['admission' => $admission->id]));

        $this->assertDatabaseHas('bills', ['admission_id' => $admission->id]);
    }
}

