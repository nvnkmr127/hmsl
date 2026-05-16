<?php

namespace App\Services\Webhooks\Factories;

use App\Models\Prescription;

class PharmacyPayloadFactory
{
    public static function forPrescription(Prescription $prescription): array
    {
        $medicines = [];
        
        // Handle both new relationship and legacy array format
        if ($prescription->items && $prescription->items->count() > 0) {
            $medicines = $prescription->items->map(fn($item) => [
                'name' => $item->medicine_name,
                'dosage' => $item->dosage,
                'duration' => $item->duration,
                'instructions' => $item->instructions,
            ])->toArray();
        } elseif (is_array($prescription->medicines)) {
            $medicines = array_map(fn($item) => [
                'name' => $item['name'] ?? ($item['medicine_name'] ?? 'Unknown'),
                'dosage' => $item['dose'] ?? ($item['dosage'] ?? 'N/A'),
                'duration' => $item['duration'] ?? 'N/A',
                'instructions' => $item['instructions'] ?? '',
            ], $prescription->medicines);
        }

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
            'medicines' => $medicines,
            'created_at' => $prescription->created_at?->toIso8601String(),
            'dispensed_at' => $prescription->dispensed_at?->toIso8601String(),
        ];
    }
}
