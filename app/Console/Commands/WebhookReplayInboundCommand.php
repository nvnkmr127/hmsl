<?php

namespace App\Console\Commands;

use App\Models\InboundWebhook;
use App\Jobs\ProcessInboundWebhookJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class WebhookReplayInboundCommand extends Command
{
    protected $signature = 'webhooks:replay-inbound {--source= : Filter by source slug} {--days=1 : Replay logs from the last X days} {--failed-only : Only replay failed processing attempts}';

    protected $description = 'Replay inbound webhooks by cloning them with a new correlation ID';

    public function handle()
    {
        $days = $this->option('days');
        $source = $this->option('source');
        $failedOnly = $this->option('failed-only');

        $query = InboundWebhook::where('created_at', '>=', now()->subDays($days))
            ->when($source, fn($q) => $q->where('source', $source))
            ->when($failedOnly, fn($q) => $q->where('status', 'failed'));

        $count = $query->count();
        
        if ($count === 0) {
            $this->info("No inbound webhooks found matching criteria.");
            return;
        }

        if (!$this->confirm("Found {$count} webhooks. Proceed with replay?")) {
            return;
        }

        $webhooks = $query->cursor();
        $processed = 0;

        foreach ($webhooks as $webhook) {
            $clone = $webhook->replicate();
            $clone->status = 'pending';
            $clone->attempt_count = 0;
            $clone->error_message = null;
            $clone->correlation_id = (string) Str::uuid();
            $clone->save();

            ProcessInboundWebhookJob::dispatch($clone);
            $processed++;
        }

        $this->info("Successfully replayed {$processed} inbound webhooks.");
    }
}
