<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Ward;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Admission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles if they don't exist
        $roles = ['doctor_owner', 'receptionist', 'nurse', 'lab_technician'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. Ensure a Department exists
        $dept = Department::firstOrCreate(['name' => 'General Medicine']);

        // 3. Create Users for each role
        $users = [
            [
                'name' => 'Dr. House',
                'email' => 'doctor@hospital.com',
                'role' => 'doctor_owner'
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

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make('password')]
            );
            $user->syncRoles([$u['role']]);

            if ($u['role'] === 'doctor_owner') {
                Doctor::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'full_name' => $user->name,
                        'specialization' => 'General Physician',
                        'department_id' => $dept->id,
                        'consultation_fee' => 500,
                        'is_active' => true
                    ]
                );
            }
        }

        // 4. Add Some Patients
        $patients = [
            ['first_name' => 'Rahul', 'last_name' => 'Sharma', 'phone' => '9888877777', 'gender' => 'male', 'date_of_birth' => '1990-05-15'],
            ['first_name' => 'Priya', 'last_name' => 'Patel', 'phone' => '9777766666', 'gender' => 'female', 'date_of_birth' => '1992-10-20'],
            ['first_name' => 'Amit', 'last_name' => 'Kumar', 'phone' => '9666655555', 'gender' => 'male', 'date_of_birth' => '1985-01-10'],
        ];

        foreach ($patients as $p) {
            if (!Patient::where('phone', $p['phone'])->exists()) {
                app(\App\Services\PatientService::class)->create($p);
            }
        }

        // 5. Add Some Bills & Revenue
        $userDoctor = User::where('email', 'doctor@hospital.com')->first();
        $doctor = $userDoctor ? $userDoctor->doctor : null;
        $patient = Patient::first();

        if ($doctor && $patient) {
            // Create a few historic bills for the dashboard
            for ($i = 1; $i <= 10; $i++) {
                $bill = app(\App\Services\BillingService::class)->createBill([
                    'patient_id' => $patient->id,
                    'discount_amount' => 50,
                    'tax_amount' => 20,
                    'payment_method' => ($i % 2 == 0) ? 'Cash' : 'UPI',
                    'payment_status' => 'Paid'
                ], [
                    ['name' => 'Consultation Fee', 'type' => 'Consultation', 'quantity' => 1, 'unit_price' => 500],
                    ['name' => 'Medicine Pack ' . $i, 'type' => 'Medicine', 'quantity' => 1, 'unit_price' => rand(100, 1000)],
                ]);
                
                // Adjust date to simulate past revenue
                $bill->update(['created_at' => now()->subDays($i)]);
            }
        }
    }
}
