<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VitalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'consultation_id' => $this->consultation_id,
            'admission_id' => $this->admission_id,
            'weight' => $this->weight,
            'height' => $this->height,
            'bmi' => $this->bmi,
            'temperature' => $this->temperature,
            'pulse' => $this->pulse,
            'bp_systolic' => $this->bp_systolic,
            'bp_diastolic' => $this->bp_diastolic,
            'resp_rate' => $this->resp_rate,
            'spo2' => $this->spo2,
            'blood_sugar' => $this->blood_sugar,
            'notes' => $this->notes,
            'recorded_by_name' => $this->recorder?->name,
            'recorded_at' => $this->created_at->toISOString(),
        ];
    }
}
