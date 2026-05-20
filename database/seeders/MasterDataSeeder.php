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
            ['email' => 'avinash.paed@hospital.com'],
            [
                'full_name' => 'Avinash Lakkampally',
                'user_id' => null, 
                'department_id' => $pediatrics->id,
                'specialization' => 'Senior Paediatrician',
                'qualification' => 'MBBS, MD (Pediatrics)',
                'phone' => '080088 02006',
                'consultation_fee' => 500,
                'registration_number' => 'REG-PED-001',
                'is_active' => true,
            ]
        );

        $services = [
            ['name' => 'General Consultation', 'category' => 'OPD', 'price' => 500, 'sort_order' => 10],
            ['name' => 'Follow-up Consultation', 'category' => 'OPD', 'price' => 300, 'sort_order' => 20],
            ['name' => 'emergency consultation/non-op(Hrs)', 'category' => 'OPD', 'price' => 500, 'sort_order' => 30],
            ['name' => 'Immunization / Vaccination', 'category' => 'OPD', 'price' => 200, 'sort_order' => 40],
            ['name' => 'Nebulization', 'category' => 'OPD', 'price' => 150, 'sort_order' => 50],
            ['name' => 'Growth & Development Monitoring', 'category' => 'OPD', 'price' => 400, 'sort_order' => 60],
            ['name' => 'new born', 'category' => 'OPD', 'price' => 800, 'sort_order' => 70],
            ['name' => 'Blood Routine (CBC)', 'category' => 'LAB', 'price' => 450, 'sort_order' => 80],
            ['name' => 'CRP Test', 'category' => 'LAB', 'price' => 600, 'sort_order' => 90],
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

        // Safe migration logic for General Ward
        $oldGeneralWard = \App\Models\Ward::where('name', 'General Ward')
            ->orWhere('name', 'General Ward A')
            ->first();
        if ($oldGeneralWard) {
            $oldGeneralWard->name = 'GENERAL WARD';
            $oldGeneralWard->daily_charge = 2700;
            $oldGeneralWard->capacity = 10;
            $oldGeneralWard->save();
            foreach ($oldGeneralWard->beds as $bed) {
                if (str_contains($bed->bed_number, 'General Ward')) {
                    $bed->bed_number = str_replace('General Ward', 'GENERAL WARD', $bed->bed_number);
                    $bed->save();
                }
            }
        }

        $wards = [
            ['name' => 'SPECIAL (SINGLE) ROOM', 'type' => 'Private', 'daily_charge' => 4000, 'capacity' => 5],
            ['name' => 'SPECIAL ROOM WITH AC', 'type' => 'Private', 'daily_charge' => 5000, 'capacity' => 5],
            ['name' => 'SHARING ROOM', 'type' => 'Semi-Private', 'daily_charge' => 3000, 'capacity' => 8],
            ['name' => 'GENERAL WARD', 'type' => 'General', 'daily_charge' => 2700, 'capacity' => 10],
            ['name' => 'PICU', 'type' => 'ICU', 'daily_charge' => 5000, 'capacity' => 5],
            ['name' => 'NICU', 'type' => 'ICU', 'daily_charge' => 5000, 'capacity' => 5],
        ];

        // Delete other wards that are NOT in the new list of names
        $newWardNames = array_column($wards, 'name');
        $wardsToDelete = \App\Models\Ward::whereNotIn('name', $newWardNames)->get();
        foreach ($wardsToDelete as $wDelete) {
            // Only delete if no beds are occupied
            $occupiedBedsCount = $wDelete->beds()->where('is_available', false)->count();
            if ($occupiedBedsCount == 0) {
                $wDelete->beds()->delete();
                $wDelete->delete();
            }
        }

        $wardManager = new \App\Services\WardService();
        foreach ($wards as $w) {
            $ward = \App\Models\Ward::updateOrCreate(['name' => $w['name']], $w);
            $wardManager->syncBeds($ward);
        }
    }
}
