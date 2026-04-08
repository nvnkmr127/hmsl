<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\PatientVital;
use App\Models\PatientVaccination;
use App\Models\PatientConsent;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\Ward;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PatientHistoryDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_history_dashboard_loads_and_tabs_show_data(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $patient = Patient::create([
            'uhid' => 'UHID-HIST-0001',
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9999999999',
            'insurance_provider' => 'ACME',
            'insurance_policy' => 'POL-1',
            'insurance_validity' => now()->addYear()->toDateString(),
            'is_active' => true,
        ]);

        $department = Department::create(['name' => 'General']);
        $doctorUser = User::factory()->create();
        $doctor = Doctor::create([
            'user_id' => null,
            'department_id' => $department->id,
            'full_name' => 'Dr Demo',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $consultation = $patient->consultations()->create([
            'doctor_id' => $doctor->id,
            'token_number' => 1,
            'consultation_date' => now()->toDateString(),
            'valid_upto' => now()->addDays(7)->toDateString(),
            'fee' => 500,
            'status' => 'Pending',
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'notes' => 'Follow up',
        ]);

        $bill = Bill::create([
            'bill_number' => 'BILL-TEST-0001',
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'subtotal' => 500,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500,
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'created_by' => $user->id,
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'item_name' => 'Consultation Fee',
            'item_type' => 'Consultation',
            'quantity' => 1,
            'unit_price' => 500,
            'total_price' => 500,
        ]);

        $ward = Ward::create(['name' => 'Ward A', 'type' => 'General', 'daily_charge' => 1000, 'capacity' => 10, 'is_active' => true]);
        $bed = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'A-01', 'is_available' => false]);

        $admission = Admission::create([
            'admission_number' => 'ADM-TEST-0001',
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDays(2),
            'reason_for_admission' => 'Observation',
            'status' => 'Discharged',
            'discharge_date' => now()->subDay(),
            'notes' => 'Discharge notes',
            'created_by' => $user->id,
        ]);

        $prescription = Prescription::create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'created_by' => $doctorUser->id,
            'diagnosis' => 'Fever',
            'medicines' => [
                ['name' => 'Paracetamol', 'dose' => '500mg', 'frequency' => 'BD', 'duration' => '3 days', 'instructions' => 'After food'],
            ],
        ]);

        $labTest = LabTest::create(['name' => 'CBC', 'category' => 'Blood', 'price' => 300, 'description' => null, 'is_active' => true]);
        LabOrder::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'lab_test_id' => $labTest->id,
            'consultation_id' => $consultation->id,
            'status' => 'Completed',
            'results' => ['hb' => '13.2'],
            'collected_at' => now()->subHours(5),
            'completed_at' => now()->subHours(1),
        ]);

        PatientVital::create([
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'temperature' => 98.6,
            'weight' => 70,
            'bp_systolic' => 120,
            'bp_diastolic' => 80,
            'spo2' => 98,
            'recorded_by' => $user->id,
        ]);

        $vaccine = Vaccine::create(['name' => 'TT', 'recommended_age' => 'Adult', 'sequence_order' => 1]);
        PatientVaccination::create(['patient_id' => $patient->id, 'vaccine_id' => $vaccine->id, 'date_given' => now()->toDateString()]);

        $this->actingAs($user)->get(route('counter.patients.history', ['id' => $patient->id]))->assertOk();

        Livewire::actingAs($user)
            ->test('counter.patient-history', ['id' => $patient->id])
            ->assertSee('OPD Visits')
            ->assertSee('History')
            ->assertSee('Bills')
            ->assertSee('Admissions')
            ->assertSee('Discharges')
            ->assertSee('Rx')
            ->assertSee('Labs')
            ->assertSee('Vitals')
            ->assertSee('Vaccines')
            ->assertSee('Consents')
            ->set('tab', 'treatment')
            ->assertSee('Treatment History')
            ->set('tab', 'billing')
            ->assertSee('Billing Records')
            ->assertSee($bill->bill_number)
            ->set('tab', 'visits')
            ->assertSee('Outpatient Visits')
            ->assertSee((string) $consultation->token_number)
            ->set('tab', 'admissions')
            ->assertSee($admission->admission_number)
            ->set('tab', 'discharges')
            ->assertSee($admission->admission_number)
            ->set('tab', 'prescriptions')
            ->assertSee('Medication Records')
            ->assertSee($prescription->diagnosis)
            ->set('tab', 'labs')
            ->assertSee('Diagnostic Reports')
            ->assertSee($labTest->name)
            ->set('tab', 'vitals')
            ->assertSee('Vital Logs')
            ->set('tab', 'vaccinations')
            ->assertSee('Vaccination Records')
            ->set('tab', 'consents')
            ->set('consentType', 'high_risk')
            ->set('consentSignedAt', now()->toDateString())
            ->set('consentNotes', 'High risk consent')
            ->set('consentFile', UploadedFile::fake()->image('high-risk-consent.jpg'))
            ->call('uploadConsent');

        $this->assertDatabaseCount('patient_consents', 1);
        $consent = PatientConsent::first();
        $this->assertSame($patient->id, $consent->patient_id);
        $this->assertSame('high_risk', $consent->type);
        $this->assertTrue(Storage::disk('public')->exists($consent->file_path));
    }
}
