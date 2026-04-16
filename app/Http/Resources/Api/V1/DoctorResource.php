<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'qualification' => $this->qualification,
            'specialization' => $this->specialization,
            'department' => [
                'id' => $this->department_id,
                'name' => $this->department?->name,
            ],
            'consultation_fee' => (float) $this->consultation_fee,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
