<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendWebhookJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $endpoint;
    public $payload;
    public $attempt;
    public $correlationId;

    public function uniqueId(): string
    {
        return $this->correlationId . '_' . $this->attempt;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(WebhookEndpoint $endpoint, array $payload, int $attempt = 1, ?string $correlationId = null)
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;
        $this->attempt = $attempt;
        $this->correlationId = $correlationId;
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $service)
    {
        // 1. Check if endpoint is still active (Circuit Breaker)
        if (!$this->endpoint->is_active) {
            return;
        }

        // 2. SSRF Protection (Final check before sending)
        if (!\App\Helpers\WebhookSecurity::isSafeUrl($this->endpoint->url)) {
            $service->logDelivery(
                $this->endpoint,
                $this->payload['event'],
                $this->payload,
                null,
                'SSRF Blocked: URL resolves to a non-public IP.',
                $this->attempt,
                null,
                null,
                $this->correlationId,
                'SSRF_BLOCKED'
            );
            $this->endpoint->update(['is_active' => false]);
            return;
        }

        // 3. Rate Limiting (20 requests per minute per endpoint)
        $rateLimitKey = 'webhook_rate_' . $this->endpoint->id;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            $this->release($seconds);
            return;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 60);

        $body = json_encode($this->payload);
        $timestamp = now()->timestamp;
        $deliveryId = $this->payload['id'] ?? (string) \Illuminate\Support\Str::uuid();
        $signature = $service->sign($body, $this->endpoint->secret, $timestamp);

        $startTime = microtime(true);
        $response = null;
        $error = null;

        try {
            $response = Http::withHeaders([
                'X-HMS-Signature' => $signature,
                'X-HMS-Event' => $this->payload['event'],
                'X-HMS-Timestamp' => $timestamp,
                'X-HMS-Delivery-ID' => $deliveryId,
                'X-HMS-Version' => $this->endpoint->api_version ?? 'v1',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout($this->endpoint->timeout_seconds ?? 10)
            ->post($this->endpoint->url, $this->payload);

            if ($response->successful()) {
                // Reset circuit breaker on success
                $this->endpoint->update(['consecutive_failures' => 0]);
            } else {
                $this->handleFailure($response->status(), $response->body());
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->handleFailure(500, $error);
        } finally {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            
            $service->logDelivery(
                $this->endpoint,
                $this->payload['event'],
                $this->payload,
                $response,
                $error,
                $this->attempt,
                $durationMs,
                $deliveryId,
                $this->correlationId,
                $response ? ($response->successful() ? null : $this->getErrorCategory($response->status(), $response->body())) : $this->getErrorCategory(500, $error)
            );
        }
    }

    protected function handleFailure($status, $error)
    {
        // Increment circuit breaker
        $this->endpoint->increment('consecutive_failures');

        // Auto-pause if too many failures (Circuit Breaker)
        if ($this->endpoint->consecutive_failures >= 10) {
            $this->endpoint->update(['is_active' => false]);
            
            // Critical notification
            \Illuminate\Support\Facades\Log::critical("Webhook Endpoint Suspended", [
                'endpoint_id' => $this->endpoint->id,
                'url' => $this->endpoint->url,
                'reason' => 'Circuit breaker triggered after 10 consecutive failures.'
            ]);
            return;
        }

        // Selective Retry logic
        // Retry only on 429 (Rate Limit) or 5xx (Server Errors)
        if ($status === 429 || ($status >= 500 && $status <= 599)) {
            $this->retryOrExecute($error);
        }
    }

    protected function retryOrExecute($error)
    {
        if ($this->attempt < 5) {
            // Exponential backoff + Jitter
            $baseDelay = pow(5, $this->attempt); 
            $jitter = rand(1, 10);
            $delay = $baseDelay + $jitter;
            
            SendWebhookJob::dispatch($this->endpoint, $this->payload, $this->attempt + 1, $this->correlationId)
                ->delay(now()->addSeconds($delay));
        }
    }

    protected function getErrorCategory($status, $error)
    {
        if ($status === 401 || $status === 403) return 'AUTH_ERROR';
        if ($status === 404) return 'NOT_FOUND';
        if ($status === 429) return 'RATE_LIMIT';
        if ($status >= 500) return 'SERVER_ERROR';
        if (str_contains((string)$error, 'SSRF')) return 'SSRF_BLOCKED';
        if (str_contains(strtolower((string)$error), 'timeout')) return 'TIMEOUT';
        
        return 'UNKNOWN_ERROR';
    }
}
