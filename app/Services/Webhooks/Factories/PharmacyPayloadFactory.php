<?php

namespace App\Services\Webhooks\Factories;

use App\Models\Prescription;

class PharmacyPayloadFactory
{
    public static function forPrescription(Prescription $prescription): array
    {
        return [
            'id' => $prescription->id,
            'patient' => [
                'id' => $prescription->patient_id,
                'name' => $prescription->patient?->full_name,
                'uhid' => $prescription->patient?->uhid,
            ],
            'doctor' => [
                'id' => $prescription->doctor_id,
                'name' => $prescription->doctor?->full_name,
            ],
            'medicines' => $prescription->items->map(fn($item) => [
                'name' => $item->medicine_name,
                'dosage' => $item->dosage,
                'duration' => $item->duration,
                'instructions' => $item->instructions,
            ])->toArray(),
            'created_at' => $prescription->created_at?->toIso8601String(),
            'dispensed_at' => $prescription->dispensed_at?->toIso8601String(),
        ];
    }
}
