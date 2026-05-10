<?php

namespace App\Console\Commands;

use App\Models\WebhookLog;
use App\Jobs\SendWebhookJob;
use Illuminate\Console\Command;

class WebhookRetryOutboundCommand extends Command
{
    protected $signature = 'webhooks:retry-outbound {--endpoint= : Filter by endpoint ID} {--event= : Filter by event name} {--days=1 : Retry failed logs from the last X days} {--limit=100 : Limit the number of retries}';

    protected $description = 'Retry failed outbound webhook deliveries';

    public function handle()
    {
        $days = $this->option('days');
        $limit = $this->option('limit');
        $endpointId = $this->option('endpoint');
        $event = $this->option('event');

        $query = WebhookLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDays($days))
            ->when($endpointId, fn($q) => $q->where('webhook_endpoint_id', $endpointId))
            ->when($event, fn($q) => $q->where('event_name', $event));

        $count = $query->count();
        
        if ($count === 0) {
            $this->info("No failed logs found matching criteria.");
            return;
        }

        if (!$this->confirm("Found {$count} failed deliveries. Proceed with retry?")) {
            return;
        }

        $logs = $query->limit($limit)->get();
        $processed = 0;

        foreach ($logs as $log) {
            if ($log->endpoint && $log->endpoint->is_active) {
                SendWebhookJob::dispatch($log->endpoint, $log->payload, $log->attempt_number + 1, $log->correlation_id);
                $processed++;
            }
        }

        $this->info("Queued {$processed} deliveries for retry.");
    }
}
