<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the admission number sequences to continue from the hospital's
 * existing patient records.
 *
 *  - PICU + all Rooms  →  'admission_ward'  seeded at 5119  (next = 5120)
 *  - NICU              →  'admission_nicu'  seeded at 3022  (next = 3023)
 *
 * Run with:  php artisan db:seed --class=AdmissionSequenceSeeder
 */
class AdmissionSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $sequences = [
            [
                'name'          => 'admission_ward',
                'scope'         => null,
                'current_value' => 5119,   // PICU + Rooms: next will be 5120
            ],
            [
                'name'          => 'admission_nicu',
                'scope'         => null,
                'current_value' => 3022,   // NICU: next will be 3023
            ],
        ];

        foreach ($sequences as $seq) {
            DB::table('number_sequences')->upsert(
                [
                    'name'          => $seq['name'],
                    'scope'         => $seq['scope'],
                    'current_value' => $seq['current_value'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                ['name', 'scope'],          // unique key columns
                ['current_value', 'updated_at']  // only update if row already exists and value is LOWER
            );
        }

        $this->command->info('Admission sequences seeded:');
        $this->command->table(
            ['Sequence', 'Seeded At', 'Next Number'],
            [
                ['admission_ward (PICU + Rooms)', 5119, 5120],
                ['admission_nicu (NICU)',         3022, 3023],
            ]
        );
    }
}
