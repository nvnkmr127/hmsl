<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WebhookService;

class WebhookDailySummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:daily-summary {date? : The date to summarize (Y-m-d). Defaults to today.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches a comprehensive daily activity summary via webhooks.';

    /**
     * Execute the console command.
     */
    public function handle(WebhookService $webhookService)
    {
        $date = $this->argument('date') ?: now()->toDateString();
        $this->info("Generating Daily Summary for {$date}...");

        $webhookService->dispatchDailySummary($date);

        $this->info("Webhook 'system.daily.summary' dispatched successfully!");
    }
}
