<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;

class RevenueReport extends Component
{
    public $from;
    public $to;
    public $paymentMethod = '';

    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function render(ReportService $reportService)
    {
        $filter = new ReportFilter(
            from: $this->from,
            to: $this->to,
            paymentMethod: $this->paymentMethod ?: null
        );

        $stats = $reportService->getRevenueStats($filter);

        return view('livewire.reports.revenue-report', [
            'stats' => $stats,
            'paymentMethods' => ['Cash', 'UPI', 'Card', 'Cheque', 'Insurance'],
        ]);
    }
}
