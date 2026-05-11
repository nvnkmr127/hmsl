<?php

namespace App\Console\Commands;

use App\Models\Consultation;
use App\Models\PatientVital;
use Illuminate\Console\Command;

class BackfillPatientVitalsFromConsultations extends Command
{
    protected $signature = 'hms:backfill-patient-vitals
        {--dry-run : Show counts only, do not write}
        {--from= : Start date (YYYY-MM-DD) for consultation_date}
        {--to= : End date (YYYY-MM-DD) for consultation_date}
        {--chunk=500 : Batch size}';

    protected $description = 'Backfill patient_vitals from consultation vitals fields for older OPD records';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $from = $this->option('from');
        $to = $this->option('to');
        $chunk = (int) $this->option('chunk');

        $query = Consultation::query()
            ->where(function ($q) {
                $q->whereNotNull('weight')
                    ->orWhereNotNull('height')
                    ->orWhereNotNull('temperature');
            })
            ->whereDoesntHave('vitals');

        if ($from) {
            $query->whereDate('consultation_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('consultation_date', '<=', $to);
        }

        $total = (clone $query)->count();
        $this->info("Consultations to backfill: {$total}");

        if ($dryRun || $total === 0) {
            return self::SUCCESS;
        }

        $created = 0;
        $query
            ->orderBy('id')
            ->chunkById($chunk, function ($consultations) use (&$created) {
                foreach ($consultations as $c) {
                    $bmi = null;
                    if ($c->weight !== null && $c->height !== null && (float) $c->height > 0) {
                        $heightInMeters = (float) $c->height / 100;
                        $bmi = round(((float) $c->weight) / ($heightInMeters * $heightInMeters), 1);
                    }

                    PatientVital::updateOrCreate(
                        [
                            'patient_id' => $c->patient_id,
                            'consultation_id' => $c->id,
                        ],
                        [
                            'recorded_by' => $c->created_by,
                            'weight' => $c->weight,
                            'height' => $c->height,
                            'bmi' => $bmi,
                            'temperature' => $c->temperature,
                        ]
                    );
                    $created++;
                }
            });

        $this->info("Backfilled patient_vitals rows: {$created}");

        return self::SUCCESS;
    }
}

