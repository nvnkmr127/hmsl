<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'generic_name' => $this->generic_name,
            'category' => $this->category,
            'strength' => $this->strength,
            'manufacturer' => $this->manufacturer,
            'price' => (float) $this->selling_price,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => $this->is_active,
        ];
    }
}
