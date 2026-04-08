<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'Dr. Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('doctor_owner');

        $department = Department::firstOrCreate(
            ['name' => 'General'],
            ['description' => 'Default department']
        );

        $doctor = Doctor::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'department_id' => $department->id,
                'full_name' => $admin->name,
                'specialization' => 'General',
                'consultation_fee' => (float) Setting::get('consultation_fee_default', 500),
                'is_active' => true,
            ]
        );

        HospitalOwner::setOwnerDoctor($doctor);
    }
}
