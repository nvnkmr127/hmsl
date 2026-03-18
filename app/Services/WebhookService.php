<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Models\WebhookLog;
use App\Jobs\SendWebhookJob;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    /**
     * Entry point to dispatch webhooks for an event.
     */
    public function dispatch(string $event, array $data)
    {
        $endpoints = $this->getSubscribedEndpoints($event);
        $payload = $this->buildPayload($event, $data);

        foreach ($endpoints as $endpoint) {
            // Queue the job
            SendWebhookJob::dispatch($endpoint, $payload);
        }
    }

    /**
     * Find active endpoints subscribed to a specific event.
     */
    public function getSubscribedEndpoints(string $event)
    {
        return WebhookEndpoint::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();
    }

    /**
     * Standardize the webhook payload envelope.
     */
    public function buildPayload(string $event, array $data)
    {
        return [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'hospital' => config('app.name', 'HMS'),
            'data' => $data
        ];
    }

    /**
     * Generate HMAC signature for a payload.
     */
    public function sign(string $payload, string $secret)
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Log a delivery attempt.
     */
    public function logDelivery(WebhookEndpoint $endpoint, string $event, array $payload, $response, $error = null, $attempt = 1)
    {
        $status = 'failed';
        if ($response && $response->successful()) {
            $status = 'success';
        } elseif ($attempt < 5) {
            $status = 'retrying';
        }

        return WebhookLog::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_name' => $event,
            'payload' => $payload,
            'response_status' => $response ? $response->status() : null,
            'response_body' => $response ? $response->body() : null,
            'attempt_number' => $attempt,
            'status' => $status,
            'error_message' => $error,
            'delivered_at' => $status === 'success' ? now() : null,
        ]);
    }
}
