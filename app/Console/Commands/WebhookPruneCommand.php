<?php

namespace App\Console\Commands;

use App\Models\WebhookLog;
use App\Models\InboundWebhook;
use App\Models\WebhookOutbox;
use Illuminate\Console\Command;

class WebhookPruneCommand extends Command
{
    protected $signature = 'webhooks:prune {--days=30 : Number of days of logs to retain} {--force : Force pruning without confirmation}';

    protected $description = 'Prune old webhook delivery logs, outbox entries, and inbound records';

    public function handle()
    {
        $days = (int) $this->option('days');
        $retentionDate = now()->subDays($days);

        if (!$this->option('force') && !$this->confirm("This will delete all webhook logs older than {$days} days. Are you sure?")) {
            return;
        }

        $this->info("Pruning records older than {$retentionDate->toDateTimeString()}...");

        $outbound = WebhookLog::where('created_at', '<', $retentionDate)->delete();
        $this->info("Deleted {$outbound} outbound delivery logs.");

        $inbound = InboundWebhook::where('created_at', '<', $retentionDate)->delete();
        $this->info("Deleted {$inbound} inbound webhook records.");

        $outbox = WebhookOutbox::where('created_at', '<', $retentionDate)->delete();
        $this->info("Deleted {$outbox} outbox entries.");

        $this->info("Pruning complete.");
    }
}
