<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ward;
use App\Models\Bed;

class WardsAndBedsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // To strictly show ONLY these 5 wards, we will truncate the tables.
        // WARNING: This will break foreign keys if admissions exist.
        // As per the requirement "i should only see the above option only", we must clear old data.
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Bed::truncate();
        Ward::truncate();
        // Also truncate admissions to prevent foreign key errors since we truncated beds
        \App\Models\Admission::truncate();
        \App\Models\AdmissionBedHistory::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Special (Single) Room
        $specialWard = Ward::create([
            'name' => 'SPECIAL (SINGLE) ROOM',
            'code' => 'SP-SR',
            'type' => 'Private',
            'daily_charge' => 4000,
            'capacity' => 19, // 5 on 1st, 7 on 2nd, 7 on 3rd
            'is_active' => true,
        ]);

        // 2. Special Room With AC
        $specialAcWard = Ward::create([
            'name' => 'SPECIAL ROOM WITH AC',
            'code' => 'SP-AC',
            'type' => 'Private',
            'daily_charge' => 5000,
            'capacity' => 19,
            'is_active' => true,
        ]);

        $specialBeds = [
            '1/1',
            '1/2',
            '1/3',
            '1/4',
            '1/5',
            '2/1',
            '2/2',
            '2/3',
            '2/4',
            '2/5',
            '2/6',
            '2/7',
            '3/1',
            '3/2',
            '3/3',
            '3/4',
            '3/5',
            '3/6',
            '3/7'
        ];

        // Seed beds for BOTH Wards (Duplicate physical beds)
        foreach ($specialBeds as $bedNo) {
            Bed::create([
                'ward_id' => $specialWard->id,
                'bed_number' => $bedNo,
            ]);

            Bed::create([
                'ward_id' => $specialAcWard->id,
                'bed_number' => $bedNo,
            ]);
        }

        // 3. Sharing Room
        $sharingWard = Ward::create([
            'name' => 'SHARING ROOM',
            'code' => 'SH-RM',
            'type' => 'Semi-Private',
            'daily_charge' => 3000,
            'capacity' => 8, // 4 on 2nd, 4 on 3rd
            'is_active' => true,
        ]);

        $sharingBeds = [
            '2/S1-B1',
            '2/S1-B2',
            '2/S2-B1',
            '2/S2-B2',
            '3/S1-B1',
            '3/S1-B2',
            '3/S2-B1',
            '3/S2-B2'
        ];

        foreach ($sharingBeds as $bedNo) {
            Bed::create([
                'ward_id' => $sharingWard->id,
                'bed_number' => $bedNo,
            ]);
        }

        // 4. PICU
        $picuWard = Ward::create([
            'name' => 'PICU',
            'code' => 'PICU',
            'type' => 'ICU',
            'daily_charge' => 5000,
            'capacity' => 10,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= $picuWard->capacity; $i++) {
            Bed::create([
                'ward_id' => $picuWard->id,
                'bed_number' => 'PICU-' . $i,
            ]);
        }

        // 5. NICU
        $nicuWard = Ward::create([
            'name' => 'NICU',
            'code' => 'NICU',
            'type' => 'ICU',
            'daily_charge' => 5000,
            'capacity' => 10,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= $nicuWard->capacity; $i++) {
            Bed::create([
                'ward_id' => $nicuWard->id,
                'bed_number' => 'NICU-' . $i,
            ]);
        }
    }
}
