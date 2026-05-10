<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookEndpointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'events' => $this->events,
            'api_version' => $this->api_version,
            'is_active' => $this->is_active,
            'consecutive_failures' => $this->consecutive_failures,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
