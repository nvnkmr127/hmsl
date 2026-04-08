<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SequenceNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_numbers_are_unique_and_increment_per_day(): void
    {
        $patient = Patient::create([
            'uhid' => 'UHID-SEQ-0001',
            'first_name' => 'Seq',
            'last_name' => 'Test',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9000000004',
            'is_active' => true,
        ]);

        $service = app(BillingService::class);

        $b1 = $service->createBill([
            'patient_id' => $patient->id,
            'payment_status' => 'Unpaid',
        ], [
            ['name' => 'Service A', 'type' => 'Other', 'quantity' => 1, 'unit_price' => 100],
        ]);

        $b2 = $service->createBill([
            'patient_id' => $patient->id,
            'payment_status' => 'Unpaid',
        ], [
            ['name' => 'Service B', 'type' => 'Other', 'quantity' => 1, 'unit_price' => 200],
        ]);

        $this->assertNotSame($b1->bill_number, $b2->bill_number);
        $this->assertStringContainsString(now()->format('Ymd'), $b1->bill_number);
        $this->assertStringContainsString(now()->format('Ymd'), $b2->bill_number);
    }
}

