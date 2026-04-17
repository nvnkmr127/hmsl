<?php

namespace Tests\Feature;

use App\Livewire\Doctor\ConsultationDesk;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorDashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_desk_layout_is_responsive_and_not_clipping_prone(): void
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
            'uhid' => 'UHID-DOCTOR-LAYOUT-0001',
            'first_name' => 'Sai',
            'last_name' => 'Kiran',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9999999999',
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'token_number' => 1,
            'consultation_date' => date('Y-m-d'),
            'fee' => 500,
            'status' => 'Pending',
            'payment_status' => 'Paid',
        ]);

        Livewire::actingAs($user)
            ->test(ConsultationDesk::class)
            ->call('selectConsultation', $consultation->id)
            ->assertSeeHtml('xl:grid-cols-4')
            ->assertSeeHtml('grid-cols-1 sm:grid-cols-2 xl:grid-cols-4')
            ->assertSeeHtml('flex flex-col xl:flex-row')
            ->assertSeeHtml('whitespace-nowrap');
    }
}

