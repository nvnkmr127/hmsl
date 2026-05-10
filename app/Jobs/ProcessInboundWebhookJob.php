<?php

namespace App\Jobs;

use App\Exceptions\WebhookValidationException;
use App\Models\InboundWebhook;
use App\Services\WebhookHandlers\WebhookHandlerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInboundWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900, 3600];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public InboundWebhook $webhook
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Propagate correlation ID for any outbound webhooks triggered by this job
        \App\Services\WebhookService::$currentCorrelationId = $this->webhook->correlation_id;

        $logContext = [
            'source' => $this->webhook->source,
            'inbound_webhook_id' => $this->webhook->id,
            'correlation_id' => $this->webhook->correlation_id,
        ];

        try {
            // Check for idempotency/duplicates first
            if ($this->isDuplicate()) {
                $this->webhook->update([
                    'status' => 'ignored',
                    'error_message' => 'Duplicate delivery ignored (idempotency)',
                ]);
                Log::info("Webhook ignored: Duplicate delivery.", $logContext);
                return;
            }

            $this->webhook->update([
                'status' => 'processing',
                'attempt_count' => $this->webhook->attempt_count + 1,
            ]);

            $handler = $this->resolveHandler();

            if ($handler) {
                $handler->handle($this->webhook);
                
                $this->webhook->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'error_category' => null,
                ]);

                Log::info("Webhook processed successfully.", $logContext);
            } else {
                $this->webhook->update([
                    'status' => 'failed',
                    'error_category' => 'missing_handler',
                    'error_message' => "No registered handler for source: {$this->webhook->source}",
                ]);
                Log::warning("Webhook failed: No handler found.", $logContext);
            }

        } catch (WebhookValidationException $e) {
            // Non-retryable failure
            $this->webhook->update([
                'status' => 'failed',
                'error_category' => 'validation_error',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Webhook failed: Validation error.", array_merge($logContext, ['error' => $e->getMessage()]));
            
            // We don't re-throw here because it's a permanent failure
        } catch (\Throwable $e) {
            // Transient failure - mark as failed and re-throw for retry
            $this->webhook->update([
                'status' => 'failed',
                'error_category' => 'transient_error',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Webhook failed: Transient error. Retrying...", array_merge($logContext, [
                'error' => $e->getMessage(),
                'attempt' => $this->webhook->attempt_count
            ]));

            throw $e;
        }
    }

    /**
     * Resolve the handler from the registry.
     */
    protected function resolveHandler(): ?WebhookHandlerInterface
    {
        $registry = [
            \App\Services\WebhookHandlers\CrmHandler::class,
            // \App\Services\WebhookHandlers\StripeHandler::class,
        ];

        foreach ($registry as $handlerClass) {
            $handler = app($handlerClass);
            if ($handler->supports($this->webhook)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Check if this webhook is a duplicate (Idempotency).
     */
    protected function isDuplicate(): bool
    {
        if (!$this->webhook->external_id) return false;

        return InboundWebhook::where('source', $this->webhook->source)
            ->where('external_id', $this->webhook->external_id)
            ->where('id', '!=', $this->webhook->id)
            ->whereIn('status', ['completed', 'processing', 'ignored'])
            ->exists();
    }
}
