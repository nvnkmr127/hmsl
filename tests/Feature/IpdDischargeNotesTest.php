<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IpdDischargeNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_ipd_discharge_flow_saves_notes(): void
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
            'uhid' => 'UHID-IPD-0009',
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
            'admission_number' => 'ADM-TEST-9001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDay(),
            'reason_for_admission' => 'Observation',
            'status' => 'Admitted',
            'created_by' => $user->id,
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
            'finalized_by' => $user->id,
            'clinical_summary' => 'Test summary',
        ]);

        $ipdService = app(\App\Services\IpdService::class);
        $billingService = app(\App\Services\BillingService::class);
        
        $admission->discharge_date = now();
        $billItems = $ipdService->buildFinalBillItems($admission);
        $bill = $billingService->upsertAdmissionFinalBill($admission, $billItems);
        $billingService->markAsPaid($bill, 'Cash');
        
        $admission->discharge_date = null;
        $admission->save();

        Livewire::actingAs($user)
            ->test('counter.ipd-admissions')
            ->call('dischargePatient', $admission->id)
            ->set('dischargeNotes', 'Discharge note test')
            ->call('confirmDischarge');

        $admission->refresh();
        $bed->refresh();

        $this->assertSame('Discharged', $admission->status);
        $this->assertNotNull($admission->discharge_date);
        $this->assertSame('Discharge note test', $admission->notes);
        $this->assertTrue((bool) $bed->is_available);

        $this->assertDatabaseHas('bills', ['admission_id' => $admission->id]);
        $bill = Bill::where('admission_id', $admission->id)->first();
        $this->assertNotNull($bill);
    }

    public function test_ipd_admissions_component_renders_with_soft_deleted_patient(): void
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

        $patient = Patient::create([
            'uhid' => 'UHID-IPD-SOFT-DELETE',
            'first_name' => 'SoftDeleted',
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
            'admission_number' => 'ADM-TEST-9002',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDay(),
            'reason_for_admission' => 'Observation',
            'status' => 'Admitted',
            'created_by' => $user->id,
        ]);

        // Soft delete the patient
        $patient->delete();

        Livewire::actingAs($user)
            ->test('counter.ipd-admissions')
            ->assertSee('SoftDeleted')
            ->assertSee('Patient')
            ->assertSee('UHID-IPD-SOFT-DELETE');
    }
}
