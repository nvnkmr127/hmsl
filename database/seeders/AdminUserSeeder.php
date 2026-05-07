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
        $admin = User::updateOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'Avinash Lakkampally',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('doctor_owner');

        $pediatrics = Department::updateOrCreate(
            ['name' => 'Pediatrics'],
            ['description' => 'Specialized medical care for infants, children, and adolescents.']
        );

        $doctor = Doctor::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'department_id' => $pediatrics->id,
                'full_name' => $admin->name,
                'specialization' => 'Senior Paediatrician',
                'consultation_fee' => (float) Setting::get('consultation_fee_default', 500),
                'is_active' => true,
            ]
        );

        HospitalOwner::setOwnerDoctor($doctor);

        // Create other staff roles for Quick Access
        $staff = [
            [
                'name' => 'House',
                'email' => 'doctor@hospital.com',
                'role' => 'doctor'
            ],
            [
                'name' => 'Janet Receptionist',
                'email' => 'counter@hospital.com',
                'role' => 'receptionist'
            ],
            [
                'name' => 'Nurse Joy',
                'email' => 'nurse@hospital.com',
                'role' => 'nurse'
            ]
        ];

        foreach ($staff as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                ['name' => $s['name'], 'password' => Hash::make('password')]
            );
            $user->syncRoles([$s['role']]);
        }
    }
}
