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
}

