<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_bill_for_patient_without_opd_token(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $patient = Patient::create([
            'uhid' => 'UHID-BILLPAT-0001',
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9999999999',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test('counter.bill-generate')
            ->call('openBillingModalForPatient', $patient->id)
            ->set('items.0.name', 'X-Ray')
            ->set('items.0.type', 'Lab')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 500)
            ->call('save');

        $this->assertDatabaseHas('bills', [
            'patient_id' => $patient->id,
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
        ]);
    }
}
