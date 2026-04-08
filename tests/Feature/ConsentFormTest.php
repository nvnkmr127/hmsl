<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_high_risk_consent_form_web_and_print_load(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $patient = Patient::create([
            'uhid' => 'UHID-CONSENT-0001',
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9999999999',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('counter.patients.consents.high-risk', ['id' => $patient->id]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('counter.patients.consents.high-risk.print', ['id' => $patient->id]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('counter.patients.consents.high-risk.en', ['id' => $patient->id]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('counter.patients.consents.high-risk.print.en', ['id' => $patient->id]))
            ->assertOk();
    }
}
