<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetryStuckOutbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hms:retry-outbox {--minutes=15 : Minutes after which a processing entry is considered stuck}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry stuck or failed webhook outbox entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        
        $stuckEntries = \App\Models\WebhookOutbox::where(function($q) use ($minutes) {
            $q->where('status', 'pending')
              ->orWhere(function($sub) use ($minutes) {
                  $sub->where('status', 'processing')
                      ->where('updated_at', '<', now()->subMinutes($minutes));
              });
        })->get();

        if ($stuckEntries->isEmpty()) {
            $this->info("No stuck outbox entries found.");
            return;
        }

        $service = app(\App\Services\WebhookService::class);

        foreach ($stuckEntries as $entry) {
            $this->info("Retrying outbox entry #{$entry->id} ({$entry->event_type})");
            
            // We bypass the outbox creation inside dispatch for retries to avoid recursion
            // So we manually call the dispatch logic
            $endpoints = $service->getSubscribedEndpoints($entry->event_type);
            
            // Use a deterministic ID for retries to support idempotency
            // Here we use outbox_uuid or similar if it existed, but we can derive it from the outbox entry
            // For now, let's assume we want to keep the same payload data
            $payload = $service->buildPayload(
                $entry->event_type, 
                $entry->payload, 
                null, // buildPayload will generate one if we don't have a stable one in DB yet
                $entry->correlation_id
            );

            foreach ($endpoints as $endpoint) {
                \App\Jobs\SendWebhookJob::dispatch($endpoint, $payload, 1, $entry->correlation_id);
            }

            $entry->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
            ]);
        }

        $this->info("Processed " . $stuckEntries->count() . " stuck entries.");
    }
}
