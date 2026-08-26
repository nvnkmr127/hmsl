<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\Consultation;
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

class ReceptionTodayStatsCardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_billing_cards_show_todays_figures_only(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('doctor_owner');

        $receptionistUser = User::factory()->create();
        $receptionistUser->assignRole('receptionist');

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $ownerUser->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalOwner::setOwnerDoctor($doctor);

        $patient = Patient::create([
            'uhid' => 'UHID-STAT-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9888877711',
            'is_active' => true,
        ]);

        // 1. Create a bill from 5 days ago (Total: 2000, Paid: 2000)
        $oldBill = Bill::create([
            'bill_number' => 'BILL-OLD-001',
            'patient_id' => $patient->id,
            'subtotal' => 2000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 2000,
            'paid_amount' => 2000,
            'balance_amount' => 0,
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
        ]);
        $oldBill->created_at = now()->subDays(5);
        $oldBill->save();

        // 2. Create a bill from Today (Total: 1000, Paid: 400, Balance: 600, Discount: 100)
        $todayBill = Bill::create([
            'bill_number' => 'BILL-TODAY-001',
            'patient_id' => $patient->id,
            'subtotal' => 1100,
            'tax_amount' => 0,
            'discount_amount' => 100,
            'total_amount' => 1000,
            'paid_amount' => 400,
            'balance_amount' => 600,
            'payment_status' => 'Partially Paid',
            'payment_method' => 'Cash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Record BillPayment for today's bill
        BillPayment::create([
            'bill_id' => $todayBill->id,
            'amount' => 400,
            'type' => 'payment',
            'method' => 'Cash',
            'received_by' => $receptionistUser->id,
            'received_at' => now(),
        ]);

        // 3. Create an OP consultation from 3 days ago and one today
        Consultation::create([
            'token_number' => 1,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->subDays(3)->toDateString(),
            'fee' => 500,
            'payment_status' => 'Paid',
            'status' => 'Completed',
            'visit_type' => 'New',
        ]);

        Consultation::create([
            'token_number' => 2,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
            'fee' => 300,
            'discount_amount' => 50,
            'payment_status' => 'Paid',
            'status' => 'Completed',
            'visit_type' => 'Review',
        ]);

        // Test Receptionist View
        $component = Livewire::actingAs($receptionistUser)
            ->test(\App\Livewire\Counter\BillingList::class);

        $component->assertViewHas('stats', function ($stats) {
            // Must ONLY reflect today's bill (1 bill, 1000 billed, 400 paid, 600 due, 100 discount, 1 unpaid)
            return $stats['total_count'] === 1
                && $stats['total_billed'] == 1000
                && $stats['total_paid'] == 400
                && $stats['total_due'] == 600
                && $stats['total_discount'] == 100
                && $stats['total_unpaid'] === 1;
        });

        $component->assertViewHas('opStats', function ($opStats) {
            // Must ONLY reflect today's OP booking (1 OP, 1 review, 1 paid, 300 revenue, 50 discount)
            return $opStats['total'] === 1
                && $opStats['review'] === 1
                && $opStats['paid'] === 1
                && $opStats['revenue'] == 300
                && $opStats['discount'] == 50;
        });

        // Test Non-Receptionist View (Doctor Owner)
        $ownerComponent = Livewire::actingAs($ownerUser)
            ->test(\App\Livewire\Counter\BillingList::class);

        $ownerComponent->assertViewHas('stats', function ($stats) {
            // Must reflect all-time bills (2 bills, 3000 billed, 2400 paid)
            return $stats['total_count'] === 2
                && $stats['total_billed'] == 3000
                && $stats['total_paid'] == 2400;
        });
    }

    public function test_receptionist_inpatient_cards_show_todays_figures_only(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('doctor_owner');

        $receptionistUser = User::factory()->create();
        $receptionistUser->assignRole('receptionist');

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $ownerUser->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalOwner::setOwnerDoctor($doctor);

        $ward = Ward::create([
            'name' => 'General Ward',
            'type' => 'General',
            'daily_charge' => 1000,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $bed1 = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'B-01', 'is_available' => false]);
        $bed2 = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'B-02', 'is_available' => false]);
        $bed3 = Bed::create(['ward_id' => $ward->id, 'bed_number' => 'B-03', 'is_available' => false]);

        $patient = Patient::create([
            'uhid' => 'UHID-STAT-002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'gender' => 'female',
            'date_of_birth' => '1995-05-05',
            'phone' => '9888877722',
            'is_active' => true,
        ]);

        // 1. Admission from 5 days ago, discharged 2 days ago
        $oldAdmission = Admission::create([
            'admission_number' => 'ADM-OLD-01',
            'patient_id' => $patient->id,
            'bed_id' => $bed1->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDays(5),
            'discharge_date' => now()->subDays(2),
            'status' => 'Discharged',
            'created_by' => $ownerUser->id,
        ]);

        // 2. Admission from today (Currently Admitted)
        $todayAdmission1 = Admission::create([
            'admission_number' => 'ADM-TODAY-01',
            'patient_id' => $patient->id,
            'bed_id' => $bed2->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now(),
            'discharge_date' => null,
            'status' => 'Admitted',
            'created_by' => $receptionistUser->id,
        ]);

        // 3. Admission from 2 days ago, discharged today
        $todayAdmission2 = Admission::create([
            'admission_number' => 'ADM-TODAY-02',
            'patient_id' => $patient->id,
            'bed_id' => $bed3->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->subDays(2),
            'discharge_date' => now(),
            'status' => 'Discharged',
            'created_by' => $receptionistUser->id,
        ]);

        // IP Bill created today
        $ipBill = Bill::create([
            'bill_number' => 'BILL-IP-001',
            'admission_id' => $todayAdmission2->id,
            'patient_id' => $patient->id,
            'subtotal' => 5000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 5000,
            'paid_amount' => 3000,
            'balance_amount' => 2000,
            'payment_status' => 'Partially Paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BillPayment::create([
            'bill_id' => $ipBill->id,
            'amount' => 3000,
            'type' => 'payment',
            'method' => 'Cash',
            'received_by' => $receptionistUser->id,
            'received_at' => now(),
        ]);

        // Test Receptionist View in IpdAdmissions
        $component = Livewire::actingAs($receptionistUser)
            ->test(\App\Livewire\Counter\IpdAdmissions::class);

        $component->assertViewHas('stats', function ($stats) {
            // Total admitted today: 1 ($todayAdmission1)
            // Currently admitted (active): 1 ($todayAdmission1)
            // Discharged today: 1 ($todayAdmission2)
            // IP Billed today: 5000
            // Collections today: 3000
            // Due today: 2000
            return $stats['total'] === 1
                && $stats['admitted'] === 1
                && $stats['discharged'] === 1
                && $stats['total_billed'] == 5000
                && $stats['collections'] == 3000
                && $stats['due'] == 2000;
        });

        // Test Doctor Owner View (sees all 3 admissions)
        $ownerComponent = Livewire::actingAs($ownerUser)
            ->test(\App\Livewire\Counter\IpdAdmissions::class);

        $ownerComponent->assertViewHas('stats', function ($stats) {
            return $stats['total'] === 3
                && $stats['discharged'] === 2;
        });
    }
}
