<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\Ward;
use App\Services\BillingService;
use App\Services\IpdService;
use App\Services\OpdService;
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

        return Doctor::create([
            'user_id' => null,
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

    private function makeOpdService(): Service
    {
        return Service::create([
            'name' => 'General Consultation',
            'category' => 'OPD',
            'price' => 500,
            'description' => null,
            'is_active' => true,
        ]);
    }

    public function test_opd_token_is_created_and_print_slip_loads(): void
    {
        $receptionist = $this->makeUserWithRole('receptionist');
        $doctor = $this->makeDoctor();
        $service = $this->makeOpdService();
        $patient = $this->makePatient('UHID-OPD-0001');

        $manager = app(OpdService::class);
        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
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
        $service = $this->makeOpdService();
        $patient = $this->makePatient('UHID-OPD-0002');

        $manager = app(OpdService::class);
        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
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
        $service = $this->makeOpdService();

        $doctor = Doctor::create([
            'user_id' => $doctorOwner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalOwner::setOwnerDoctor($doctor);

        $patient = $this->makePatient('UHID-OPD-0003');
        $manager = app(OpdService::class);
        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
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
        HospitalOwner::setOwnerDoctor($doctor);

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

        \App\Models\DischargeSummary::create([
            'admission_id' => $admission->id,
            'admission_number' => $admission->admission_number,
            'admission_date' => $admission->admission_date,
            'patient_id' => $patient->id,
            'uhid' => $patient->uhid,
            'doctor_id' => $doctor->id,
            'status' => 'Finalized',
            'is_finalized' => true,
            'finalized_at' => now(),
            'finalized_by' => $doctorOwner->id,
            'clinical_summary' => 'Test summary',
        ]);

        $manager = app(IpdService::class);
        $billingService = app(BillingService::class);
        
        $admission->discharge_date = now();
        $billItems = $manager->buildFinalBillItems($admission);
        $bill = $billingService->upsertAdmissionFinalBill($admission, $billItems);
        $billingService->markAsPaid($bill, 'Cash');
        
        $admission->discharge_date = null;
        $admission->save();

        $manager->dischargePatient($admission->fresh(['bed']), 'Discharge instructions');

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

    public function test_opd_booking_component_allows_custom_edited_pricing(): void
    {
        $receptionist = $this->makeUserWithRole('receptionist');
        $doctor = $this->makeDoctor();
        $service = $this->makeOpdService();
        $patient = $this->makePatient('UHID-OPD-0099');

        // Test OpdBooking Livewire component with edited custom price
        \Livewire\Livewire::actingAs($receptionist)
            ->test('counter.opd-booking')
            ->call('selectPatient', $patient->id)
            ->set('selectedService', $service->id)
            ->set('selectedDoctor', $doctor->id)
            ->set('fee', 400.00) // Custom edited price
            ->call('book')
            ->assertDispatched('notify', function ($name, $data) {
                $payload = $data[0] ?? [];
                return ($payload['type'] ?? '') === 'success';
            });

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $patient->id,
            'fee' => 400.00,
        ]);
    }

    public function test_post_discharge_free_op_benefit(): void
    {
        $this->seedRoles();
        $doctor = $this->makeDoctor();
        $service = $this->makeOpdService();
        $patient = $this->makePatient('UHID-OPD-FREE-01');

        $ward = Ward::create([
            'name' => 'General Ward',
            'type' => 'General',
            'daily_charge' => 1000,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'G-01', 'is_available' => false]);
        
        $admission = Admission::create([
            'admission_number' => 'ADM-FREE-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => '2026-06-05 10:00:00',
            'discharge_date' => '2026-06-08 10:00:00', // Monday
            'reason_for_admission' => 'Observation',
            'status' => 'Discharged',
            'created_by' => 1,
        ]);

        $manager = app(OpdService::class);
        
        // Pick Wednesday (2026-06-10) for booking to avoid Sunday rule
        $weekdayDate = '2026-06-10';

        $consultation = $manager->bookAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'fee' => 0,
            'consultation_date' => $weekdayDate,
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
        ]);

        $this->assertSame(0.0, (float)$consultation->fee);
        $this->assertSame('Post-Discharge Follow-up', $consultation->visit_type);
    }
}

