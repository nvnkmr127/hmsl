<?php

namespace App\Services\Webhooks\Factories;

use App\Models\Consultation;

class OpdPayloadFactory
{
    public static function forConsultation(Consultation $consultation): array
    {
        return [
            'consultation_id' => $consultation->id,
            'consultation_number' => $consultation->consultation_number,
            'patient_uhid' => $consultation->patient?->uhid,
            'doctor_name' => $consultation->doctor?->full_name,
            'visit_type' => $consultation->visit_type,
            'fees' => (float) $consultation->fee,
            'status' => $consultation->status,
            'date' => $consultation->consultation_date,
            'created_at' => $consultation->created_at->toISOString(),
        ];
    }
}
