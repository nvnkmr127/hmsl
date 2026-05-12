<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Models\WebhookOutbox;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Helpers\WebhookSecurity;
use Illuminate\Support\Str;

class SendWebhookJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public WebhookEndpoint $endpoint,
        public array $payload,
        public int $attempt = 1,
        public ?string $correlationId = null
    ) {}

    public function uniqueId(): string
    {
        return $this->correlationId . '_' . $this->endpoint->id . '_' . $this->attempt;
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $service): void
    {
        $deliveryId = (string) Str::uuid();
        $startTime = microtime(true);
        
        // 1. SSRF Protection
        if (!WebhookSecurity::isSafeUrl($this->endpoint->url)) {
            $this->handleFailure($deliveryId, null, 0, 'SSRF_BLOCKED', 'URL blocked by SSRF protection policy.');
            return;
        }

        // 2. Circuit Breaker Check
        if (!$this->endpoint->is_active && $this->endpoint->consecutive_failures >= 15) {
            \Illuminate\Support\Facades\Log::warning("Skipping delivery to paused endpoint: {$this->endpoint->url}");
            return;
        }

        // 3. Request Size Limit (Max 10MB)
        $payloadSize = strlen(json_encode($this->payload));
        if ($payloadSize > 10 * 1024 * 1024) {
            $this->handleFailure($deliveryId, null, 0, 'CLIENT_ERROR', 'Request payload exceeds 10MB limit.');
            return;
        }

        $timestamp = time();
        $signature = app(\App\Services\WebhookService::class)->sign(json_encode($this->payload), $this->endpoint->secret, $timestamp);

        try {
            $response = Http::timeout($this->endpoint->timeout_seconds ?? 30)
                ->withHeaders([
                    'X-HMS-Event' => $this->payload['event'],
                    'X-HMS-Delivery-ID' => $deliveryId,
                    'X-HMS-Correlation-ID' => $this->correlationId,
                    'X-HMS-Signature' => $signature,
                    'X-HMS-Timestamp' => $timestamp,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'HMS-Webhook-Dispatcher/1.0',
                ])
                ->post($this->endpoint->url, $this->payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $this->handleSuccess($deliveryId, $response, $durationMs);
            } else {
                $this->handleFailure($deliveryId, $response, $durationMs);
            }
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->handleFailure($deliveryId, null, $durationMs, null, $e->getMessage());
        }
    }

    protected function handleSuccess(string $deliveryId, $response, int $durationMs): void
    {
        $this->endpoint->update([
            'consecutive_failures' => 0,
            'last_success_at' => now(),
        ]);

        app(WebhookService::class)->logDelivery(
            $this->endpoint,
            $this->payload['event'],
            $this->payload,
            $response,
            null,
            $this->attempt,
            $durationMs,
            $deliveryId,
            $this->correlationId,
            null,
            $response ? $response->headers() : null
        );
    }

    protected function handleFailure(string $deliveryId, $response, int $durationMs, ?string $forcedCategory = null, ?string $forcedError = null): void
    {
        $status = $response ? $response->status() : 0;
        $error = $forcedError ?? ($response ? $response->body() : 'Connection failed');
        $category = $forcedCategory ?? $this->categorizeError($status, $error);

        // Truncate large response bodies
        $truncatedError = Str::limit($error, 1000);

        $this->endpoint->increment('consecutive_failures');
        $this->endpoint->update(['last_failure_at' => now()]);

        // Auto-pause if too many consecutive failures
        if ($this->endpoint->consecutive_failures >= 15) {
            $this->endpoint->update(['is_active' => false]);
            \Illuminate\Support\Facades\Log::critical("Webhook Endpoint [{$this->endpoint->id}] paused due to 15+ failures.", [
                'endpoint_url' => $this->endpoint->url,
                'correlation_id' => $this->correlationId,
                'last_error' => $truncatedError
            ]);
        }

        app(WebhookService::class)->logDelivery(
            $this->endpoint,
            $this->payload['event'],
            $this->payload,
            $response,
            $truncatedError,
            $this->attempt,
            $durationMs,
            $deliveryId,
            $this->correlationId,
            $category,
            $response ? $response->headers() : null
        );

        // Retry logic: Retry on 429 or 5xx or connection issues
        if ($this->shouldRetry($status, $category) && $this->attempt < 5) {
            $this->scheduleRetry();
        }
    }

    protected function categorizeError(int $status, string $error): string
    {
        if ($status === 401 || $status === 403) return 'AUTH_ERROR';
        if ($status === 429) return 'RATE_LIMIT';
        if ($status >= 400 && $status < 500) return 'CLIENT_ERROR';
        if ($status >= 500) return 'SERVER_ERROR';
        
        $errorLower = strtolower($error);
        if (str_contains($errorLower, 'timeout')) return 'TIMEOUT';
        if (str_contains($errorLower, 'resolve') || str_contains($errorLower, 'dns')) return 'DNS_ERROR';
        if (str_contains($errorLower, 'ssl') || str_contains($errorLower, 'certificate')) return 'SSL_ERROR';
        if (str_contains($errorLower, 'ssrf')) return 'SSRF_BLOCKED';

        return 'UNKNOWN';
    }

    protected function shouldRetry(int $status, string $category): bool
    {
        // Don't retry on AUTH_ERROR or CLIENT_ERROR (except 429)
        if ($category === 'AUTH_ERROR' || ($category === 'CLIENT_ERROR' && $status !== 429)) {
            return false;
        }

        return true;
    }

    protected function scheduleRetry(): void
    {
        // Exponential backoff + Jitter
        // Attempt 1: 30s + rand(1, 10)
        // Attempt 2: 150s + rand(1, 30)
        // Attempt 3: 750s + rand(1, 60)
        $delaySeconds = pow(5, $this->attempt) * 6 + rand(1, 10 * $this->attempt);
        
        self::dispatch($this->endpoint, $this->payload, $this->attempt + 1, $this->correlationId)
            ->delay(now()->addSeconds($delaySeconds));
    }

    protected function markEndpointFailed(string $category, string $message): void
    {
        app(WebhookService::class)->logDelivery(
            $this->endpoint,
            $this->payload['event'],
            $this->payload,
            null,
            $message,
            $this->attempt,
            0,
            (string) Str::uuid(),
            $this->correlationId,
            $category
        );

        $this->endpoint->update(['is_active' => false]);
    }
}
