<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new PatientService();

        $patients = [
            [
                'first_name' => 'Amit',
                'last_name' => 'Sharma',
                'gender' => 'Male',
                'date_of_birth' => '1985-05-15',
                'phone' => '9876543210',
                'email' => 'amit.sharma@example.com',
                'blood_group' => 'A+',
                'address' => 'Flat 402, Sunshine Apartments',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'pincode' => '110001',
                'emergency_contact_name' => 'Priya Sharma',
                'emergency_contact_phone' => '9876543211',
            ],
            [
                'first_name' => 'Sneha',
                'last_name' => 'Reddy',
                'gender' => 'Female',
                'date_of_birth' => '1992-08-22',
                'phone' => '9000011111',
                'email' => 'sneha.reddy@example.com',
                'blood_group' => 'O+',
                'address' => 'H.No 12-3/A, Jubilee Hills',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'pincode' => '500033',
            ],
            [
                'first_name' => 'Rahul',
                'last_name' => 'Verma',
                'gender' => 'Male',
                'date_of_birth' => '1978-01-10',
                'phone' => '8888877777',
                'blood_group' => 'B+',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
            ]
        ];

        foreach ($patients as $p) {
            $p['uhid'] = $service->generateUHID();
            Patient::create($p);
        }
    }
}
