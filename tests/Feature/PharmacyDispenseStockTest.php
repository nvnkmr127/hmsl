<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PharmacyDispenseStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispensing_prescription_decrements_medicine_stock_and_logs_transaction(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Rx',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'uhid' => 'UHID-RX-0001',
            'first_name' => 'Rx',
            'last_name' => 'Patient',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9000000003',
            'is_active' => true,
        ]);

        $medicine = Medicine::create([
            'name' => 'Paracetamol',
            'category' => 'Tablet',
            'strength' => '500mg',
            'selling_price' => 10,
            'stock_quantity' => 10,
            'min_stock_level' => 2,
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'token_number' => 1,
            'consultation_date' => now()->toDateString(),
            'fee' => 500,
            'status' => 'Completed',
            'payment_status' => 'Paid',
        ]);

        $prescription = Prescription::create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'created_by' => $user->id,
            'medicines' => [
                [
                    'medicine_id' => $medicine->id,
                    'name' => $medicine->name,
                    'qty' => 2,
                    'dose' => '500mg',
                    'frequency' => 'Once a day',
                    'duration' => '2 days',
                    'instructions' => 'After food',
                ],
            ],
            'is_dispensed' => false,
        ]);

        Livewire::actingAs($user)
            ->test('pharmacy.pharmacy-orders')
            ->call('dispense', $prescription->id);

        $medicine->refresh();
        $prescription->refresh();

        $this->assertTrue((bool) $prescription->is_dispensed);
        $this->assertSame(8, (int) $medicine->stock_quantity);
        $this->assertDatabaseHas('medicine_stock_transactions', [
            'medicine_id' => $medicine->id,
            'type' => 'dispense',
        ]);
    }
}
