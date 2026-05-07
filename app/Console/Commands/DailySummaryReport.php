<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Admission;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Events\System\DailySummaryGenerated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $summary = [
            'report_date' => $date,
            'metrics' => [
                'new_patients' => Patient::whereDate('created_at', $date)->count(),
                'opd_total' => Consultation::whereDate('consultation_date', $date)->count(),
                'opd_new' => Consultation::whereDate('consultation_date', $date)->where('visit_type', 'New')->count(),
                'opd_review' => Consultation::whereDate('consultation_date', $date)->whereIn('visit_type', ['Review', 'Follow-up'])->count(),
                'ipd_admissions' => Admission::whereDate('admission_date', $date)->count(),
                'revenue' => [
                    'total_collected' => BillPayment::whereDate('received_at', $date)->sum('amount'),
                    'total_invoiced' => Bill::whereDate('created_at', $date)->sum('total_amount'),
                    'discounts' => Bill::whereDate('created_at', $date)->sum('discount_amount'),
                ],
                'clinical' => [
                    'completed_consultations' => Consultation::whereDate('consultation_date', $date)->where('status', 'Completed')->count(),
                    'ongoing_consultations' => Consultation::whereDate('consultation_date', $date)->where('status', 'Ongoing')->count(),
                ]
            ],
            'generation_time' => now()->toIso8601String()
        ];

        event(new DailySummaryGenerated($summary));

        $this->info("Daily Summary dispatched successfully.");
    }
}
