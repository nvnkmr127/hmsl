<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicalTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            'Acute Bronchiolitis',
            'Gastroenteritis (Dehydration)',
            'Febrile Convulsions',
            'Pneumonia',
            'Asthma Exacerbation',
            'Neonatal Jaundice (Phototherapy)',
            'Severe Acute Malnutrition (SAM)',
            'Post-Surgical Recovery',
        ];

        $notes = [
            'Awaiting pediatrician review.',
            'Monitor vitals every 4 hours.',
            'IV fluids started as per pediatric protocol.',
            'Nebulization started.',
            'Feeding status: NPO.',
            'Oxygen support required (Low flow).',
        ];

        foreach ($reasons as $reason) {
            \App\Models\ClinicalTemplate::create(['type' => 'reason', 'content' => $reason]);
        }

        foreach ($notes as $note) {
            \App\Models\ClinicalTemplate::create(['type' => 'notes', 'content' => $note]);
        }

        $discharge = [
            'Follow up after 1 week at OPD.',
            'Continue prescribed medicines for 5 days.',
            'Report to emergency if high fever occurs.',
            'Normal diet / Feeding resumed.',
            'Stitch removal on [Date].',
            'Pediatric consultation needed after 3 days.',
        ];

        foreach ($discharge as $d) {
            \App\Models\ClinicalTemplate::create(['type' => 'discharge', 'content' => $d]);
        }
    }
}
