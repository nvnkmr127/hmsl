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
    public function dispatch(string $event, array $data, ?string $correlationId = null)
    {
        $correlationId = $correlationId ?? (string) \Illuminate\Support\Str::uuid();

        // 1. Record in Outbox first (Source of Truth)
        $outbox = \App\Models\WebhookOutbox::create([
            'correlation_id' => $correlationId,
            'event_type' => $event,
            'payload' => $data,
            'status' => 'pending'
        ]);

        $endpoints = $this->getSubscribedEndpoints($event);
        $payload = $this->buildPayload($event, $data);

        if ($endpoints->isEmpty()) {
            $outbox->update(['status' => 'dispatched', 'dispatched_at' => now()]);
            return;
        }

        $outbox->update(['status' => 'processing']);

        foreach ($endpoints as $endpoint) {
            // Queue the job
            SendWebhookJob::dispatch($endpoint, $payload, 1, $correlationId);
        }

        $outbox->update(['status' => 'dispatched', 'dispatched_at' => now()]);
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
     * Generate HMAC signature for a payload with timestamp.
     */
    public function sign(string $payload, string $secret, int $timestamp)
    {
        $signedPayload = $timestamp . '.' . $payload;
        return 'sha256=' . hash_hmac('sha256', $signedPayload, $secret);
    }

    /**
     * Log a delivery attempt.
     */
    public function logDelivery(
        WebhookEndpoint $endpoint, 
        string $event, 
        array $payload, 
        $response, 
        $error = null, 
        $attempt = 1,
        $durationMs = null,
        $deliveryId = null,
        $correlationId = null,
        $errorCategory = null
    ) {
        $status = 'failed';
        if ($response && $response->successful()) {
            $status = 'success';
        } elseif ($attempt < 5) {
            $status = 'retrying';
        }

        return WebhookLog::create([
            'webhook_endpoint_id' => $endpoint->id,
            'delivery_id' => $deliveryId,
            'correlation_id' => $correlationId,
            'event_name' => $event,
            'payload' => $payload,
            'response_status' => $response ? $response->status() : null,
            'response_body' => $response ? $response->body() : null,
            'duration_ms' => $durationMs,
            'attempt_number' => $attempt,
            'status' => $status,
            'error_message' => $error,
            'error_category' => $errorCategory,
            'delivered_at' => $status === 'success' ? now() : null,
        ]);
    }
}
