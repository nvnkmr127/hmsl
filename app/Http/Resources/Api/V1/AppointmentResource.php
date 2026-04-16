<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token_number' => $this->token_number,
            'consultation_date' => $this->consultation_date?->format('Y-m-d'),
            'valid_upto' => $this->valid_upto?->format('Y-m-d'),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'fee' => (float) $this->fee,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'vitals' => [
                'weight' => $this->weight,
                'temperature' => $this->temperature,
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
