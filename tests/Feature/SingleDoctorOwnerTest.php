<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\User;
use App\Actions\Fortify\CreateNewUser;
use App\Services\DoctorService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SingleDoctorOwnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_can_access_admin_pages_even_with_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $owner = User::factory()->create();
        $owner->assignRole('doctor_owner');

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalOwner::setOwnerDoctor($doctor);

        $staff = User::factory()->create();
        $staff->assignRole('receptionist');
        $staff->givePermissionTo('manage settings');
        $staff->givePermissionTo('manage master data');
        $staff->givePermissionTo('manage users');

        $this->actingAs($staff)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('master.doctors.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('master.users.index'))->assertForbidden();
    }

    public function test_registration_is_blocked_after_owner_exists(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $owner = User::factory()->create(['email' => 'owner@demo.test']);
        $owner->assignRole('doctor_owner');

        $department = Department::create(['name' => 'General']);
        $doctor = Doctor::create([
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalOwner::setOwnerDoctor($doctor);

        $this->expectException(ValidationException::class);
        app(CreateNewUser::class)->create([
            'name' => 'New User',
            'email' => 'new@demo.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    public function test_doctor_service_blocks_multiple_doctor_accounts(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $department = Department::create(['name' => 'General']);

        $owner = User::factory()->create();
        $owner->assignRole('doctor_owner');

        $service = app(DoctorService::class);
        $service->create([
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Owner',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);

        $otherOwner = User::factory()->create();
        $otherOwner->assignRole('doctor_owner');

        $this->expectException(ValidationException::class);
        $service->create([
            'user_id' => $otherOwner->id,
            'department_id' => $department->id,
            'full_name' => 'Dr Other',
            'specialization' => 'General',
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
    }
}
