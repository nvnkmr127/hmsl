<?php

namespace App\Services\Webhooks\Factories;

use Illuminate\Support\Str;

class WebhookPayloadFactory
{
    /**
     * Build the standard envelope for any webhook event.
     */
    public static function createEnvelope(string $event, array $data, ?string $correlationId = null): array
    {
        return [
            'id' => (string) Str::uuid(),
            'event' => $event,
            'api_version' => config('webhooks.api_version', '1.0.0'),
            'timestamp' => now()->toISOString(),
            'correlation_id' => $correlationId ?? (string) Str::uuid(),
            'environment' => config('app.env'),
            'hospital' => config('webhooks.hospital'),
            'data' => $data,
        ];
    }
}
