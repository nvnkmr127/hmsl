<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneWebhookLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hms:prune-webhooks {--days=7 : Days of logs to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old webhook delivery and inbound logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $outboundCount = \App\Models\WebhookLog::where('created_at', '<', $date)->delete();
        $inboundCount = \App\Models\InboundWebhook::where('created_at', '<', $date)->delete();
        $outboxCount = \App\Models\WebhookOutbox::where('created_at', '<', $date)
            ->where('status', 'dispatched')
            ->delete();

        $this->info("Successfully pruned {$outboundCount} outbound, {$inboundCount} inbound logs, and {$outboxCount} outbox entries older than {$days} days.");
    }
}
