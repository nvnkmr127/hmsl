<?php

namespace App\Console\Commands;

use App\Events\System\DailySummaryGenerated;
use App\Services\Webhooks\Factories\SystemPayloadFactory;
use Illuminate\Console\Command;

class DailySummaryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hms:report-summary {date? : The date for the report in YYYY-MM-DD format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and dispatch a daily activity summary via webhooks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->argument('date') ?: now()->format('Y-m-d');
        $this->info("Generating Daily Summary for: {$date}");

        $summary = SystemPayloadFactory::createDailySummary($date);

        event(new DailySummaryGenerated($summary));

        $this->info("Daily Summary dispatched successfully.");
    }
}
