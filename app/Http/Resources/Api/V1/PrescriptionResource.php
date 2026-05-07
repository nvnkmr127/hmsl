<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient_name' => $this->patient?->full_name,
            'doctor_id' => $this->doctor_id,
            'doctor_name' => $this->doctor?->full_name,
            'consultation_id' => $this->consultation_id,
            'medicines' => $this->medicines,
            'is_dispensed' => $this->is_dispensed,
            'dispensed_at' => $this->dispensed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
