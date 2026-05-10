<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_id' => $this->delivery_id,
            'correlation_id' => $this->correlation_id,
            'event_name' => $this->event_name,
            'payload' => $this->payload,
            'response_status' => $this->response_status,
            'response_body' => $this->response_body,
            'duration_ms' => $this->duration_ms,
            'status' => $this->status,
            'attempt_number' => $this->attempt_number,
            'error_category' => $this->error_category,
            'error_message' => $this->error_message,
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,
        ];
    }
}
