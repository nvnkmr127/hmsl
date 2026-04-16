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
            // Processing logic based on source and payload event
            // Example:
            // if ($source === 'external_service' && ($payload['event'] ?? '') === 'patient.update') {
            //    // Handle patient update
            // }

            $this->webhook->update([
                'status' => 'processed',
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
