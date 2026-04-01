<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Ward;
use App\Models\Bed;
use App\Models\Admission;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\IpdManager;
use Tests\TestCase;

class FeatureSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get(route('dashboard'))->assertRedirect('/login');
        $this->get(route('counter.patients.index'))->assertRedirect('/login');
        $this->get(route('counter.opd.index'))->assertRedirect('/login');
        $this->get(route('billing.index'))->assertRedirect('/login');
        $this->get(route('discharge.index'))->assertRedirect('/login');
    }

    public function test_dashboard_loads_for_authenticated_roles(): void
    {
        foreach (['doctor_owner', 'receptionist', 'nurse', 'lab_technician', 'pharmacist', 'accountant'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('dashboard'))->assertOk();
        }
    }

    public function test_receptionist_can_access_reception_pages(): void
    {
        $user = $this->userWithRole('receptionist');

        $this->actingAs($user)->get(route('counter.patients.index'))->assertOk();
        $this->actingAs($user)->get(route('counter.opd.index'))->assertOk();
        $this->actingAs($user)->get(route('billing.index'))->assertOk();

        $this->actingAs($user)->get(route('doctor.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('discharge.index'))->assertForbidden();
        $this->actingAs($user)->get(route('inventory.index'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
    }

    public function test_patient_history_page_loads_for_receptionist(): void
    {
        $user = $this->userWithRole('receptionist');

        $patient = Patient::create([
            'uhid' => 'UHID-TEST-0001',
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9999999999',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('counter.patients.history', ['id' => $patient->id]))->assertOk();
    }

    public function test_nurse_can_access_ipd_pages(): void
    {
        $user = $this->userWithRole('nurse');

        $this->actingAs($user)->get(route('counter.ipd.index'))->assertOk();
        $this->actingAs($user)->get(route('counter.ipd.create'))->assertOk();
        $this->actingAs($user)->get(route('billing.index'))->assertForbidden();
    }

    public function test_doctor_owner_can_access_clinical_and_admin_pages(): void
    {
        $user = $this->userWithRole('doctor_owner');

        $this->actingAs($user)->get(route('doctor.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('doctor.appointments.index'))->assertOk();
        $this->actingAs($user)->get(route('counter.opd.index'))->assertOk();
        $this->actingAs($user)->get(route('billing.index'))->assertOk();
        $this->actingAs($user)->get(route('discharge.index'))->assertOk();
        $this->actingAs($user)->get(route('settings.index'))->assertOk();
        $this->actingAs($user)->get(route('reports.index'))->assertRedirect(route('reports.revenue'));
    }

    public function test_discharge_service_updates_admission_and_frees_bed(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $creator = User::factory()->create()->assignRole('doctor_owner');

        $department = Department::create(['name' => 'General']);
        $doctorUser = User::factory()->create();
        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Test',
            'specialization' => 'General',
            'consultation_fee' => 100,
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'uhid' => 'UHID-TEST-0002',
            'first_name' => 'IPD',
            'last_name' => 'Patient',
            'gender' => 'female',
            'date_of_birth' => '1990-01-01',
            'phone' => '9888877777',
            'is_active' => true,
        ]);

        $ward = Ward::create(['name' => 'Ward A', 'type' => 'General', 'daily_charge' => 1000, 'capacity' => 10, 'is_active' => true]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'A-01', 'is_available' => false]);

        $admission = Admission::create([
            'admission_number' => 'ADM-TEST-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now(),
            'reason_for_admission' => 'Observation',
            'status' => 'Admitted',
            'created_by' => $creator->id,
        ]);

        $manager = app(IpdManager::class);
        $manager->dischargePatient($admission->fresh(['bed']), 'Test discharge');

        $admission->refresh();
        $bed->refresh();

        $this->assertSame('Discharged', $admission->status);
        $this->assertNotNull($admission->discharge_date);
        $this->assertSame('Test discharge', $admission->notes);
        $this->assertTrue((bool) $bed->is_available);
    }

    public function test_lab_technician_can_access_laboratory_pages(): void
    {
        $user = $this->userWithRole('lab_technician');

        $this->actingAs($user)->get(route('laboratory.index'))->assertOk();
        $this->actingAs($user)->get(route('laboratory.tests'))->assertOk();
        $this->actingAs($user)->get(route('laboratory.results'))->assertOk();

        $this->actingAs($user)->get(route('pharmacy.index'))->assertForbidden();
        $this->actingAs($user)->get(route('inventory.index'))->assertForbidden();
    }

    public function test_pharmacist_can_access_pharmacy_and_inventory(): void
    {
        $user = $this->userWithRole('pharmacist');

        $this->actingAs($user)->get(route('pharmacy.index'))->assertOk();
        $this->actingAs($user)->get(route('pharmacy.orders'))->assertOk();
        $this->actingAs($user)->get(route('pharmacy.stock'))->assertOk();

        $this->actingAs($user)->get(route('inventory.index'))->assertOk();
        $this->actingAs($user)->get(route('inventory.stock'))->assertOk();
        $this->actingAs($user)->get(route('inventory.suppliers'))->assertOk();

        $this->actingAs($user)->get(route('laboratory.index'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
    }

    public function test_accountant_can_access_reports_and_billing(): void
    {
        $user = $this->userWithRole('accountant');

        $this->actingAs($user)->get(route('billing.index'))->assertOk();
        $this->actingAs($user)->get(route('reports.index'))->assertRedirect(route('reports.revenue'));
        $this->actingAs($user)->get(route('reports.revenue'))->assertOk();

        $this->actingAs($user)->get(route('inventory.index'))->assertForbidden();
        $this->actingAs($user)->get(route('laboratory.index'))->assertForbidden();
        $this->actingAs($user)->get(route('settings.index'))->assertForbidden();
    }
}
