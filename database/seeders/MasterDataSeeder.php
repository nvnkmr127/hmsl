<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pediatrics = Department::updateOrCreate(
            ['name' => 'Pediatrics'], 
            ['description' => 'Specialized medical care for infants, children, and adolescents.']
        );
        
        $adminUser = \App\Models\User::where('email', 'admin@hospital.com')->first();
        
        Doctor::updateOrCreate(
            ['email' => 'pediatrician@hospital.com'],
            [
                'full_name' => 'Dr. ' . ($adminUser->name ?? 'Specialist'),
                'user_id' => $adminUser->id ?? null,
                'department_id' => $pediatrics->id,
                'specialization' => 'Senior Pediatrician',
                'qualification' => 'MBBS, MD (Pediatrics)',
                'phone' => '+91 9876543210',
                'consultation_fee' => 500,
                'registration_number' => 'REG-PED-001',
                'is_active' => true,
            ]
        );

        $services = [
            ['name' => 'Consultation - GP', 'category' => 'OPD', 'price' => 500],
            ['name' => 'Consultation - Specialist', 'category' => 'OPD', 'price' => 800],
            ['name' => 'X-Ray Chest', 'category' => 'RADIO', 'price' => 1200],
            ['name' => 'Blood Routine (CBC)', 'category' => 'LAB', 'price' => 450],
            ['name' => 'ECG', 'category' => 'LAB', 'price' => 600],
            ['name' => 'Ward Bed Charge (General)', 'category' => 'IPD', 'price' => 1500],
            ['name' => 'ICU Charge', 'category' => 'IPD', 'price' => 5000],
        ];

        foreach ($services as $service) {
            \App\Models\Service::updateOrCreate(['name' => $service['name']], $service);
        }

        $medicines = [
            ['name' => 'Dolo 650', 'generic_name' => 'Paracetamol', 'category' => 'Tablet', 'strength' => '650mg', 'selling_price' => 30, 'stock_quantity' => 500, 'min_stock_level' => 50],
            ['name' => 'Amoxicillin', 'generic_name' => 'Amoxicillin', 'category' => 'Capsule', 'strength' => '500mg', 'selling_price' => 120, 'stock_quantity' => 20, 'min_stock_level' => 50],
            ['name' => 'Benadryl', 'generic_name' => 'Diphenhydramine', 'category' => 'Syrup', 'strength' => '100ml', 'selling_price' => 150, 'stock_quantity' => 100, 'min_stock_level' => 20],
            ['name' => 'Insulin Glargine', 'generic_name' => 'Insulin', 'category' => 'Injection', 'strength' => '10ml', 'selling_price' => 850, 'stock_quantity' => 5, 'min_stock_level' => 10],
        ];

        foreach ($medicines as $med) {
            \App\Models\Medicine::updateOrCreate(['name' => $med['name']], $med);
        }

        $labTests = [
            [
                'name' => 'Complete Blood Count (CBC)',
                'category' => 'Hematology',
                'price' => 450,
                'parameters' => [
                    ['name' => 'Hemoglobin', 'unit' => 'g/dL', 'reference_range' => '13.5 - 17.5'],
                    ['name' => 'WBC Count', 'unit' => '/cmm', 'reference_range' => '4000 - 11000'],
                    ['name' => 'Platelet Count', 'unit' => 'lakhs/cmm', 'reference_range' => '1.5 - 4.5'],
                ]
            ],
            [
                'name' => 'Lipid Profile',
                'category' => 'Biochemistry',
                'price' => 1200,
                'parameters' => [
                    ['name' => 'Total Cholesterol', 'unit' => 'mg/dL', 'reference_range' => '< 200'],
                    ['name' => 'HDL Cholesterol', 'unit' => 'mg/dL', 'reference_range' => '> 40'],
                    ['name' => 'LDL Cholesterol', 'unit' => 'mg/dL', 'reference_range' => '< 100'],
                ]
            ],
        ];

        foreach ($labTests as $lt) {
            $test = \App\Models\LabTest::updateOrCreate(
                ['name' => $lt['name']],
                ['category' => $lt['category'], 'price' => $lt['price']]
            );
            
            $test->parameters()->delete();
            foreach ($lt['parameters'] as $param) {
                $test->parameters()->create($param);
            }
        }

        $wards = [
            ['name' => 'General Ward A', 'type' => 'General', 'daily_charge' => 1500, 'capacity' => 10],
            ['name' => 'Cardiac ICU', 'type' => 'ICU', 'daily_charge' => 8000, 'capacity' => 5],
            ['name' => 'Private Deluxe 101', 'type' => 'Private', 'daily_charge' => 4500, 'capacity' => 1],
            ['name' => 'Private Deluxe 102', 'type' => 'Private', 'daily_charge' => 4500, 'capacity' => 1],
            ['name' => 'Emergency Room', 'type' => 'ER', 'daily_charge' => 2000, 'capacity' => 8],
        ];

        $wardManager = new \App\Services\WardService();
        foreach ($wards as $w) {
            $ward = \App\Models\Ward::updateOrCreate(['name' => $w['name']], $w);
            if ($ward->wasRecentlyCreated || $ward->beds()->count() == 0) {
                $wardManager->generateBeds($ward);
            }
        }
    }
}
