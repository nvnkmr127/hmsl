<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'daily_charge' => (float) $this->daily_charge,
            'capacity' => $this->capacity,
            'available_beds' => $this->whenLoaded('beds', function () {
                return $this->beds->where('status', 'available')->count();
            }),
            'is_active' => $this->is_active,
        ];
    }
}
