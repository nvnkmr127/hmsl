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
            ->assertSee('Final Bill Not Generated');

        $this->actingAs($user)
            ->post(route('discharge.final-bill', ['admission' => $admission->id]))
            ->assertRedirect(route('discharge.summary', ['admission' => $admission->id]));

        $this->assertDatabaseHas('bills', ['admission_id' => $admission->id]);
    }

    public function test_discharge_flow_safeguards_and_successful_completion(): void
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
            'uhid' => 'UHID-IPD-BILL-0002',
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
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'A-02', 'is_available' => false]);

        $admission = Admission::create([
            'admission_number' => 'ADM-FINAL-0002',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDay(),
            'reason_for_admission' => 'Observation',
            'status' => 'Admitted',
            'created_by' => $user->id,
        ]);

        $ipdService = app(\App\Services\IpdService::class);
        $summaryService = app(\App\Services\DischargeSummaryService::class);
        $billingService = app(\App\Services\BillingService::class);

        // 1. Attempt discharge without summary -> should fail due to safety guard
        try {
            $ipdService->dischargePatient($admission);
            $this->fail('Discharged patient without finalized summary.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('A Finalized Discharge Summary is mandatory before discharge', $e->getMessage());
        }

        // 2. Create summary in Draft -> should still fail because it is not finalized
        $summary = $summaryService->createDraft($admission);
        try {
            $ipdService->dischargePatient($admission);
            $this->fail('Discharged patient with draft (unfinalized) summary.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('A Finalized Discharge Summary is mandatory before discharge', $e->getMessage());
        }

        // 3. Finalize summary
        $summary->update([
            'final_diagnosis' => 'Accidental injury',
            'treatment_summary' => 'Splint applied',
            'condition_at_discharge' => 'Stable',
        ]);
        $summaryService->finalize($summary, $user);

        // 4. Generate final bill
        $billItems = $ipdService->buildFinalBillItems($admission);
        $bill = $billingService->upsertAdmissionFinalBill($admission, $billItems);

        // 5. Attempt discharge with unpaid final bill -> should fail due to billing guard
        try {
            $ipdService->dischargePatient($admission);
            $this->fail('Discharged patient with unpaid bill.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Settle the bill first', $e->getMessage());
        }

        // 6. Pay the bill
        $billingService->markAsPaid($bill, 'Cash');

        // 7. Settle discharge now -> should succeed
        $dischargedAdmission = $ipdService->dischargePatient($admission);

        $this->assertSame('Discharged', $dischargedAdmission->status);
        $this->assertNotNull($dischargedAdmission->discharge_date);
        
        $bed->refresh();
        $this->assertTrue((bool) $bed->is_available);
    }

    public function test_discharge_process_component_displays_patient_admission_start_date_and_time_correctly_for_joined_ward(): void
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
            'uhid' => 'UHID-IPD-BILL-0003',
            'first_name' => 'Admitted',
            'last_name' => 'Patient',
            'gender' => 'female',
            'date_of_birth' => '1990-01-01',
            'phone' => '9888877777',
            'is_active' => true,
        ]);

        $ward = Ward::create([
            'name' => 'Deluxe Ward',
            'type' => 'General',
            'daily_charge' => 1500,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'D-101', 'is_available' => false]);

        $admissionDate = '2026-08-20 14:35:00';

        $admission = Admission::create([
            'admission_number' => 'ADM-START-DATE-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => $admissionDate,
            'reason_for_admission' => 'Treatment',
            'status' => 'Admitted',
            'created_by' => $user->id,
        ]);

        \App\Models\AdmissionBedHistory::create([
            'admission_id' => $admission->id,
            'bed_id' => $bed->id,
            'start_time' => $admissionDate,
            'daily_charge' => 1500,
        ]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Ipd\DischargeProcess::class, ['admission' => $admission]);

        $bedCharges = $component->get('bedCharges');
        $this->assertNotEmpty($bedCharges);
        $this->assertSame((int) $ward->id, (int) $bedCharges[0]['ward_id']);
        $this->assertSame('2026-08-20T14:35', $bedCharges[0]['start_date']);
    }

    public function test_discharge_process_displays_admission_datetime_even_with_preexisting_bill_and_no_bed_history(): void
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
            'uhid' => 'UHID-IPD-BILL-0004',
            'first_name' => 'Admitted2',
            'last_name' => 'Patient2',
            'gender' => 'male',
            'date_of_birth' => '1995-05-05',
            'phone' => '9888877776',
            'is_active' => true,
        ]);

        $ward = Ward::create([
            'name' => 'ICU',
            'type' => 'General',
            'daily_charge' => 2500,
            'capacity' => 5,
            'is_active' => true,
        ]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'ICU-1', 'is_available' => false]);

        $admissionDate = '2026-08-15 08:20:00';

        $admission = Admission::create([
            'admission_number' => 'ADM-START-DATE-0002',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => $admissionDate,
            'reason_for_admission' => 'Critical Care',
            'status' => 'Admitted',
            'created_by' => $user->id,
        ]);

        // Pre-create a bill as IpdController::show does automatically
        app(\App\Services\IpdService::class)->ensureFinalBill($admission);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Ipd\DischargeProcess::class, ['admission' => $admission]);

        $bedCharges = $component->get('bedCharges');
        $this->assertNotEmpty($bedCharges);
        $this->assertSame((int) $ward->id, (int) $bedCharges[0]['ward_id']);
        $this->assertSame('2026-08-15T08:20', $bedCharges[0]['start_date']);
    }

    public function test_add_bed_charge_ensures_end_date_is_greater_than_or_equal_to_start_date(): void
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
            'uhid' => 'UHID-IPD-BILL-0005',
            'first_name' => 'Admitted3',
            'last_name' => 'Patient3',
            'gender' => 'male',
            'date_of_birth' => '1995-05-05',
            'phone' => '9888877775',
            'is_active' => true,
        ]);

        $ward = Ward::create([
            'name' => 'Special Ward',
            'type' => 'General',
            'daily_charge' => 4000,
            'capacity' => 5,
            'is_active' => true,
        ]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'SP-1', 'is_available' => false]);

        $admission = Admission::create([
            'admission_number' => 'ADM-START-DATE-0003',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->format('Y-m-d H:i:s'),
            'reason_for_admission' => 'General Care',
            'status' => 'Admitted',
            'created_by' => $user->id,
        ]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Ipd\DischargeProcess::class, ['admission' => $admission]);

        // Set row 1 end_date into the future (e.g. tomorrow)
        $futureDate = now()->addDays(2)->format('Y-m-d\TH:i');
        $component->set('bedCharges.0.end_date', $futureDate);

        // Click Add Bed Charge
        $component->call('addBedCharge');

        $bedCharges = $component->get('bedCharges');
        $this->assertCount(2, $bedCharges);

        // Row 2 start_date must equal row 1 end_date
        $this->assertSame($futureDate, $bedCharges[1]['start_date']);
        // Row 2 end_date must be >= start_date
        $this->assertTrue(
            \Illuminate\Support\Carbon::parse($bedCharges[1]['end_date'])->greaterThanOrEqualTo(\Illuminate\Support\Carbon::parse($bedCharges[1]['start_date']))
        );
    }
}




