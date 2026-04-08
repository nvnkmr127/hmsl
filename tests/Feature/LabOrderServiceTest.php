<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use App\Services\LabOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_grouped_lab_orders_with_order_number(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Lab',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'uhid' => 'UHID-LAB-0001',
            'first_name' => 'Lab',
            'last_name' => 'Patient',
            'gender' => 'female',
            'date_of_birth' => '1990-01-01',
            'phone' => '9000000002',
            'is_active' => true,
        ]);

        $t1 = LabTest::create(['name' => 'CBC', 'category' => 'Hematology', 'price' => 300, 'is_active' => true]);
        $t2 = LabTest::create(['name' => 'RBS', 'category' => 'Biochemistry', 'price' => 150, 'is_active' => true]);

        $service = app(LabOrderService::class);
        $orders = $service->createOrders([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'notes' => 'Test order',
        ], [$t1->id, $t2->id]);

        $this->assertCount(2, $orders);
        $this->assertNotNull($orders[0]->order_number);
        $this->assertSame($orders[0]->order_number, $orders[1]->order_number);
        $this->assertNotNull($orders[0]->group_uuid);
        $this->assertSame($orders[0]->group_uuid, $orders[1]->group_uuid);
        $this->assertDatabaseCount('lab_orders', 2);
    }
}
