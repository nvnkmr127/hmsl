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
    protected $signature = 'hms:report-summary {date? : The date for the report in YYYY-MM-DD format} {--shift= : The shift name (e.g. Day or Night)}';

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
        $date = $this->argument('date');
        $shift = $this->option('shift');

        if (!$date) {
            $date = ($shift === 'Night')
                ? now()->subDay()->format('Y-m-d')
                : now()->format('Y-m-d');
        }
        
        $label = $date . ($shift ? " ({$shift} summary)" : "");
        $this->info("Generating Daily Summary for: {$label}");

        $summary = SystemPayloadFactory::createDailySummary($date, $shift);

        event(new DailySummaryGenerated($summary));

        $this->info("Daily Summary dispatched successfully.");
    }
}
