<?php

namespace App\Jobs;

use App\Models\InboundWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhook;

    /**
     * Create a new job instance.
     */
    public function __construct(InboundWebhook $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payload = $this->webhook->payload;
        $source = $this->webhook->source;

        Log::info("Processing webhook from {$source}", ['payload' => $payload]);

        try {
            // 1. Dynamic Handler Lookup
            // Logic: App\Services\WebhookHandlers\{StudlySource}Handler
            $handlerClass = "App\\Services\\WebhookHandlers\\" . \Illuminate\Support\Str::studly($source) . "Handler";
            
            if (class_exists($handlerClass)) {
                $handler = app($handlerClass);
                $handler->handle($this->webhook);
            } else {
                Log::warning("No specific handler found for webhook source: {$source}. Mark as completed.");
            }

            $this->webhook->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed for ID {$this->webhook->id}: " . $e->getMessage());
            $this->webhook->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
