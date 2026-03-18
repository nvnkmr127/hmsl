<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaccinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vaccines = [
            ['name' => 'BCG', 'target_disease' => 'Tuberculosis', 'recommended_age' => 'At Birth', 'sequence_order' => 1],
            ['name' => 'OPV-0', 'target_disease' => 'Polio', 'recommended_age' => 'At Birth', 'sequence_order' => 2],
            ['name' => 'Hepatitis B', 'target_disease' => 'Hepatitis B', 'recommended_age' => 'At Birth', 'sequence_order' => 3],
            ['name' => 'DPT-1', 'target_disease' => 'Diphtheria, Pertussis, Tetanus', 'recommended_age' => '6 Weeks', 'sequence_order' => 4],
            ['name' => 'OPV-1', 'target_disease' => 'Polio', 'recommended_age' => '6 Weeks', 'sequence_order' => 5],
            ['name' => 'Rotavirus-1', 'target_disease' => 'Diarrhea', 'recommended_age' => '6 Weeks', 'sequence_order' => 6],
            ['name' => 'MMR-1', 'target_disease' => 'Measles, Mumps, Rubella', 'recommended_age' => '9 Months', 'sequence_order' => 7],
            ['name' => 'Typhoid', 'target_disease' => 'Typhoid', 'recommended_age' => '2 Years', 'sequence_order' => 8],
        ];

        foreach ($vaccines as $vaccine) {
            \App\Models\Vaccine::updateOrCreate(['name' => $vaccine['name']], $vaccine);
        }
    }
}
