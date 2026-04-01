<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use App\Services\BillingService;
use App\Services\IpdManager;
use App\Services\OpdManager;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalFlowsTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    private function makeUserWithRole(string $role): User
    {
        $this->seedRoles();

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeDoctor(): Doctor
    {
        $department = Department::create(['name' => 'General Medicine']);
        $user = User::factory()->create();

        return Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Demo',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
    }

    private function makePatient(string $uhid): Patient
    {
        return Patient::create([
            'uhid' => $uhid,
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9999999999',
            'is_active' => true,
        ]);
    }

    public function test_opd_token_is_created_and_print_slip_loads(): void
    {
        $receptionist = $this->makeUserWithRole('receptionist');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient('UHID-OPD-0001');

        $manager = app(OpdManager::class);
        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'fee' => 500,
            'consultation_date' => date('Y-m-d'),
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'notes' => 'Test booking',
        ]);

        $this->assertSame(1, $consultation->token_number);

        $this->actingAs($receptionist)
            ->get(route('counter.opd.print', ['id' => $consultation->id]))
            ->assertOk();
    }

    public function test_opd_token_cancel_updates_status(): void
    {
        $this->seedRoles();
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient('UHID-OPD-0002');

        $manager = app(OpdManager::class);
        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'fee' => 500,
            'consultation_date' => date('Y-m-d'),
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
        ]);

        $manager->updateStatus($consultation, 'Cancelled');

        $consultation->refresh();
        $this->assertSame('Cancelled', $consultation->status);
    }

    public function test_doctor_dashboard_renders_queue_items(): void
    {
        $doctorOwner = $this->makeUserWithRole('doctor_owner');
        $department = Department::create(['name' => 'General']);

        $doctor = Doctor::create([
            'user_id' => $doctorOwner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $patient = $this->makePatient('UHID-OPD-0003');
        $manager = app(OpdManager::class);
        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'fee' => 500,
            'consultation_date' => date('Y-m-d'),
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
        ]);

        $this->actingAs($doctorOwner)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee((string) $consultation->token_number)
            ->assertSee($patient->full_name);
    }

    public function test_bill_receipt_print_loads(): void
    {
        $receptionist = $this->makeUserWithRole('receptionist');
        $patient = $this->makePatient('UHID-BILL-0001');

        $service = app(BillingService::class);

        $this->actingAs($receptionist);
        $bill = $service->createBill([
            'patient_id' => $patient->id,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
        ], [
            ['name' => 'Consultation Fee', 'type' => 'Consultation', 'quantity' => 1, 'unit_price' => 500],
        ]);

        $this->assertInstanceOf(Bill::class, $bill);
        $this->assertNotEmpty($bill->bill_number);

        $this->get(route('billing.bills.print', ['bill' => $bill->id]))->assertOk();
        $this->get(route('counter.bills.print', ['id' => $bill->id]))->assertOk();
    }

    public function test_discharge_summary_screen_and_print_load_for_discharged_admission(): void
    {
        $doctorOwner = $this->makeUserWithRole('doctor_owner');

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $doctorOwner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'uhid' => 'UHID-IPD-0001',
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
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'A-01', 'is_available' => false]);

        $admission = Admission::create([
            'admission_number' => 'ADM-TEST-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now(),
            'reason_for_admission' => 'Observation',
            'status' => 'Admitted',
            'created_by' => $doctorOwner->id,
        ]);

        $manager = app(IpdManager::class);
        $manager->dischargePatient($admission, 'Discharge instructions');

        $this->actingAs($doctorOwner)
            ->get(route('discharge.summary', ['admission' => $admission->id]))
            ->assertOk()
            ->assertSee($admission->admission_number)
            ->assertSee($patient->full_name);

        $this->actingAs($doctorOwner)
            ->get(route('discharge.print', ['admission' => $admission->id]))
            ->assertOk()
            ->assertSee($admission->admission_number);
    }
}

