<?php

namespace App\Services\Webhooks\Factories;

use App\Models\Admission;

class IpdPayloadFactory
{
    public static function forAdmission(Admission $admission): array
    {
        return [
            'id' => $admission->id,
            'admission_number' => $admission->admission_number,
            'status' => $admission->status,
            'patient' => [
                'id' => $admission->patient_id,
                'uhid' => $admission->patient->uhid,
                'full_name' => $admission->patient->full_name,
            ],
            'doctor' => [
                'id' => $admission->doctor_id,
                'full_name' => $admission->doctor->full_name,
            ],
            'location' => [
                'ward' => $admission->bed?->ward?->name,
                'bed' => $admission->bed?->bed_number,
            ],
            'is_emergency' => $admission->is_emergency,
            'admitted_at' => $admission->admission_date?->toIso8601String(),
            'discharged_at' => $admission->discharge_date?->toIso8601String(),
        ];
    }
}
