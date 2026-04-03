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
                'first_name' => 'Sai',
                'last_name' => 'Kiran',
                'gender' => 'Male',
                'date_of_birth' => now()->subYears(5)->format('Y-m-d'),
                'phone' => '9848012345',
                'blood_group' => 'B+',
                'address' => 'Varni Road',
                'city' => 'Nizamabad',
                'state' => 'Telangana',
                'pincode' => '503001',
            ],
            [
                'first_name' => 'Ananya',
                'last_name' => 'Reddy',
                'gender' => 'Female',
                'date_of_birth' => now()->subYears(2)->format('Y-m-d'),
                'phone' => '9000011111',
                'blood_group' => 'O+',
                'address' => 'Khaleelwadi',
                'city' => 'Nizamabad',
                'state' => 'Telangana',
                'pincode' => '503003',
            ],
            [
                'first_name' => 'Vihaan',
                'last_name' => 'Goud',
                'gender' => 'Male',
                'date_of_birth' => now()->subYears(10)->format('Y-m-d'),
                'phone' => '8888877777',
                'blood_group' => 'A+',
                'address' => 'Tilak Gardens',
                'city' => 'Nizamabad',
                'state' => 'Telangana',
            ]
        ];

        foreach ($patients as $p) {
            $p['uhid'] = $service->generateUHID();
            Patient::create($p);
        }
    }
}
