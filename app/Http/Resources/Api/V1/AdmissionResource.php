<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admission_number' => $this->admission_number,
            'patient_id' => $this->patient_id,
            'patient_name' => $this->patient?->full_name,
            'doctor_id' => $this->doctor_id,
            'doctor_name' => $this->doctor?->full_name,
            'bed_id' => $this->bed_id,
            'bed_name' => $this->bed?->name,
            'ward_name' => $this->ward_name,
            'admission_date' => $this->admission_date?->toISOString(),
            'discharge_date' => $this->discharge_date?->toISOString(),
            'status' => $this->status,
            'reason' => $this->reason_for_admission,
            'is_emergency' => $this->is_emergency,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
