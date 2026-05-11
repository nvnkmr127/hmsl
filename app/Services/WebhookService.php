<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Models\WebhookLog;
use App\Models\WebhookOutbox;
use App\Jobs\SendWebhookJob;
use App\Services\Webhooks\Factories\WebhookPayloadFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookService
{
    /**
     * Entry point to dispatch webhooks for an event.
     */
    public function dispatch(string $event, array $data, ?string $correlationId = null): void
    {
        // Validate event against catalog
        $event = trim($event);
        $catalog = config('webhooks.events', []);
        
        if (!isset($catalog[$event])) {
            Log::warning("Attempted to dispatch unregistered webhook event: '{$event}'", [
                'correlation_id' => $correlationId,
                'event_length' => strlen($event),
                'available_events' => array_keys($catalog)
            ]);
        }

        $correlationId = $correlationId ?? $this->resolveCorrelationId();

        // 1. Record in Outbox first (Source of Truth)
        $outbox = WebhookOutbox::create([
            'correlation_id' => $correlationId,
            'event_type' => $event,
            'payload' => $data,
            'status' => 'pending'
        ]);

        $endpoints = $this->getSubscribedEndpoints($event);
        
        // 2. Build the standard envelope
        $payload = WebhookPayloadFactory::createEnvelope($event, $data, $correlationId);

        if ($endpoints->isEmpty()) {
            $outbox->update(['status' => 'dispatched', 'dispatched_at' => now()]);
            return;
        }

        $outbox->update(['status' => 'processing']);

        foreach ($endpoints as $endpoint) {
            // Queue the delivery job
            SendWebhookJob::dispatch($endpoint, $payload, 1, $correlationId)->afterCommit();
        }

        // The outbox is considered "dispatched" once we've handed off to the queue
        // In a more complex system, we might wait for all deliveries to finish.
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
     * Resolve correlation ID from request headers, global state, or generate a new one.
     */
    protected function resolveCorrelationId(): string
    {
        return static::$currentCorrelationId 
            ?? request()->header('X-Correlation-ID') 
            ?? request()->header('X-Request-ID') 
            ?? (string) Str::uuid();
    }

    /**
     * Global state for correlation ID propagation in background jobs.
     */
    public static ?string $currentCorrelationId = null;

    /**
     * Generate HMAC signature for a payload with timestamp.
     */
    public function sign(string $payload, string $secret, int $timestamp): string
    {
        $signedPayload = $timestamp . '.' . $payload;
        return 'sha256=' . hash_hmac('sha256', $signedPayload, $secret);
    }

    /**
     * Log a delivery attempt with sensitive data redaction.
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
        $errorCategory = null,
        ?array $responseHeaders = null
    ) {
        $status = 'failed';
        if ($response && $response->successful()) {
            $status = 'success';
        } elseif ($attempt < 5 && ($errorCategory !== 'AUTH_ERROR' && $errorCategory !== 'CLIENT_ERROR')) {
            $status = 'retrying';
        }

        // Redact sensitive data before logging
        $redactedPayload = $this->redactPayload($payload);
        $redactedResponseHeaders = $this->redactHeaders($responseHeaders ?? []);

        return WebhookLog::create([
            'webhook_endpoint_id' => $endpoint->id,
            'delivery_id' => $deliveryId,
            'correlation_id' => $correlationId,
            'event_name' => $event,
            'payload' => $redactedPayload,
            'request_headers' => [
                'X-HMS-Event' => $event,
                'X-HMS-Delivery-ID' => $deliveryId,
                'X-HMS-Correlation-ID' => $correlationId,
            ],
            'response_status' => $response ? $response->status() : null,
            'response_body' => $response ? Str::limit($response->body(), 2000) : null,
            'response_headers' => $redactedResponseHeaders,
            'duration_ms' => $durationMs,
            'attempt_number' => $attempt,
            'status' => $status,
            'error_message' => $error,
            'error_category' => $errorCategory,
            'delivered_at' => $status === 'success' ? now() : null,
        ]);
    }

    /**
     * Redact sensitive fields in payload.
     */
    public function redactPayload(array $data): array
    {
        $sensitiveKeys = ['phone', 'email', 'mobile', 'address', 'password', 'token', 'secret', 'aadhar', 'pan', 'identifier'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys) && is_string($value)) {
                $value = Str::mask($value, '*', 3);
            }
        });

        return $data;
    }

    /**
     * Redact sensitive headers.
     */
    public function redactHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-api-key', 'set-cookie', 'cookie', 'x-hms-signature'];

        return collect($headers)->map(function ($value, $key) use ($sensitiveHeaders) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                return '[REDACTED]';
            }
            return $value;
        })->toArray();
    }

    /**
     * Dispatch the daily summary to all subscribed endpoints.
     */
    public function dispatchDailySummary(?string $date = null): void
    {
        $data = \App\Services\Webhooks\Factories\SystemPayloadFactory::createDailySummary($date);
        $this->dispatch('system.daily.summary', $data);
    }
}
