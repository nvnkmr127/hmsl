<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_payment_status_updates_with_partial_payments(): void
    {
        $patient = Patient::create([
            'uhid' => 'UHID-PAY-0001',
            'first_name' => 'Pay',
            'last_name' => 'Test',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9000000001',
            'is_active' => true,
        ]);

        $service = app(BillingService::class);

        $bill = $service->createBill([
            'patient_id' => $patient->id,
            'payment_status' => 'Unpaid',
            'payment_method' => 'Cash',
        ], [
            ['name' => 'Service A', 'type' => 'Other', 'quantity' => 1, 'unit_price' => 1000],
        ]);

        $bill->refresh();
        $this->assertSame('Unpaid', $bill->payment_status);
        $this->assertSame(1000.0, (float) $bill->total_amount);

        $service->recordPayment($bill, 200, 'Cash', 'payment', null, null);
        $service->recalculatePaymentStatus($bill);
        $bill->refresh();
        $this->assertSame('Partially Paid', $bill->payment_status);

        $service->recordPayment($bill, 800, 'Cash', 'payment', null, null);
        $service->recalculatePaymentStatus($bill);
        $bill->refresh();
        $this->assertSame('Paid', $bill->payment_status);

        $this->assertDatabaseCount('bill_payments', 2);
    }
}

