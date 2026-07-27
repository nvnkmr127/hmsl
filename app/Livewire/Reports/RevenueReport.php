<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BillPayment;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueReport extends Component
{
    use WithPagination;

    public $from;
    public $to;
    public $search = '';
    public $paymentMethod = '';
    public $paymentType = '';
    public $billCategory = '';
    public $dateBasis = 'payment'; // 'payment' or 'invoice'
    public $viewMode = 'transactions'; // 'transactions', 'staff', 'intelligence'

    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'search' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
        'paymentType' => ['except' => ''],
        'billCategory' => ['except' => ''],
        'dateBasis' => ['except' => 'payment'],
        'viewMode' => ['except' => 'transactions'],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'search', 'paymentMethod', 'paymentType', 'billCategory', 'dateBasis', 'viewMode'])) {
            $this->resetPage();
        }
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'paymentMethod', 'paymentType', 'billCategory', 'dateBasis', 'viewMode']);
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
        $this->resetPage();
    }

    public function exportCsv()
    {
        $query = $this->getPaymentsQuery();
        $payments = $query->get();

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Payment Date', 'Bill No', 'Patient Name', 'UHID', 'Phone', 
                'Category', 'Method', 'Type', 'Received By', 'Amount (₹)'
            ]);

            foreach ($payments as $payment) {
                $bill = $payment->bill;
                $category = 'Direct';
                if ($bill && $bill->consultation_id) $category = 'OPD';
                elseif ($bill && $bill->admission_id) $category = 'IPD';

                fputcsv($handle, [
                    $payment->received_at ? $payment->received_at->format('d M Y, h:i A') : 'N/A',
                    $bill ? $bill->bill_number : 'N/A',
                    $bill && $bill->patient ? $bill->patient->full_name : 'N/A',
                    $bill && $bill->patient ? $bill->patient->uhid : 'N/A',
                    $bill && $bill->patient ? $bill->patient->phone : 'N/A',
                    $category,
                    $payment->method ?? 'Cash',
                    ucfirst($payment->type),
                    $payment->receiver ? $payment->receiver->name : 'System',
                    $payment->type === 'refund' ? -$payment->amount : $payment->amount,
                ]);
            }
            fclose($handle);
        }, 'revenue_intelligence_' . now()->format('Ymd_His') . '.csv');
    }

    private function getPaymentsQuery()
    {
        return BillPayment::query()
            ->with(['bill.patient', 'receiver'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('bill', function ($bq) {
                        $bq->where('bill_number', 'like', "%{$this->search}%")
                           ->orWhereHas('patient', function ($pq) {
                               $pq->where('first_name', 'like', "%{$this->search}%")
                                  ->orWhere('last_name', 'like', "%{$this->search}%")
                                  ->orWhere('uhid', 'like', "%{$this->search}%")
                                  ->orWhere('phone', 'like', "%{$this->search}%");
                           });
                    })->orWhere('reference', 'like', "%{$this->search}%");
                });
            })
            ->when($this->paymentMethod, fn($q) => $q->where('method', $this->paymentMethod))
            ->when($this->paymentType, fn($q) => $q->where('type', $this->paymentType))
            ->when($this->billCategory === 'opd', fn($q) => $q->whereHas('bill', fn($bq) => $bq->whereNotNull('consultation_id')))
            ->when($this->billCategory === 'ipd', fn($q) => $q->whereHas('bill', fn($bq) => $bq->whereNotNull('admission_id')))
            ->when($this->billCategory === 'direct', fn($q) => $q->whereHas('bill', fn($bq) => $bq->whereNull('consultation_id')->whereNull('admission_id')))
            ->when($this->from || $this->to, function ($q) {
                if ($this->dateBasis === 'invoice') {
                    $q->whereHas('bill', function ($bq) {
                        if ($this->from) $bq->whereDate('created_at', '>=', $this->from);
                        if ($this->to) $bq->whereDate('created_at', '<=', $this->to);
                    });
                } else {
                    if ($this->from) $q->whereDate('received_at', '>=', $this->from);
                    if ($this->to) $q->whereDate('received_at', '<=', $this->to);
                }
            });
    }

    public function render(ReportService $reportService)
    {
        $paymentsQuery = $this->getPaymentsQuery();

        $grossCollection = (float) (clone $paymentsQuery)->where('type', 'payment')->sum('amount');
        $totalRefunds = (float) (clone $paymentsQuery)->where('type', 'refund')->sum('amount');
        $netCollection = $grossCollection - $totalRefunds;
        $totalTransactions = (clone $paymentsQuery)->count();

        // Invoicing & Discount Metrics for Period
        $billsInPeriod = Bill::query()
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('created_at', '<=', $this->to));
        
        $totalInvoiced = (float) (clone $billsInPeriod)->sum('total_amount');
        $totalDiscounts = (float) (clone $billsInPeriod)->sum('discount_amount');
        $totalDuesInPeriod = (float) (clone $billsInPeriod)->sum('balance_amount');
        $uniquePatientsCount = (clone $paymentsQuery)
            ->whereHas('bill', fn($bq) => $bq->whereNotNull('patient_id'))
            ->join('bills', 'bill_payments.bill_id', '=', 'bills.id')
            ->distinct('bills.patient_id')
            ->count('bills.patient_id');

        // Intelligence KPI Ratios
        $daysCount = max(1, Carbon::parse($this->from)->diffInDays(Carbon::parse($this->to)) + 1);
        $realizationRate = $totalInvoiced > 0 ? round(($grossCollection / $totalInvoiced) * 100, 1) : 100.0;
        $arpp = $uniquePatientsCount > 0 ? round($netCollection / $uniquePatientsCount, 2) : 0;
        $dailyVelocity = round($netCollection / $daysCount, 2);
        $discountLeakage = ($totalInvoiced + $totalDiscounts) > 0 ? round(($totalDiscounts / ($totalInvoiced + $totalDiscounts)) * 100, 1) : 0;

        // Payment Method Breakdown
        $methodBreakdown = (clone $paymentsQuery)
            ->select('method', DB::raw('SUM(CASE WHEN type = "payment" THEN amount ELSE -amount END) as total'))
            ->groupBy('method')
            ->get()
            ->pluck('total', 'method')
            ->toArray();

        // Revenue by Bill Category (OPD, IPD, Direct)
        $revenueByType = [
            'OPD' => (float) (clone $paymentsQuery)->whereHas('bill', fn($bq) => $bq->whereNotNull('consultation_id'))->where('type', 'payment')->sum('amount'),
            'IPD' => (float) (clone $paymentsQuery)->whereHas('bill', fn($bq) => $bq->whereNotNull('admission_id'))->where('type', 'payment')->sum('amount'),
            'Direct' => (float) (clone $paymentsQuery)->whereHas('bill', fn($bq) => $bq->whereNull('consultation_id')->whereNull('admission_id'))->where('type', 'payment')->sum('amount'),
        ];

        // Service Line Item Breakdown (Consultation, Pharmacy, Lab, Bed, Nursing, etc.)
        $serviceLineBreakdown = BillItem::query()
            ->whereHas('bill', function ($bq) {
                if ($this->dateBasis === 'invoice') {
                    if ($this->from) $bq->whereDate('created_at', '>=', $this->from);
                    if ($this->to) $bq->whereDate('created_at', '<=', $this->to);
                } else {
                    $bq->whereHas('payments', function ($pq) {
                        if ($this->from) $pq->whereDate('received_at', '>=', $this->from);
                        if ($this->to) $pq->whereDate('received_at', '<=', $this->to);
                    });
                }
            })
            ->select('item_type', DB::raw('SUM(total_price) as total'))
            ->groupBy('item_type')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'item_type')
            ->toArray();

        // Staff Collection Intelligence Breakdown
        $staffCollections = (clone $paymentsQuery)
            ->select('received_by', DB::raw('SUM(CASE WHEN type = "payment" THEN amount ELSE -amount END) as net_collected'), DB::raw('COUNT(*) as tx_count'))
            ->groupBy('received_by')
            ->with('receiver')
            ->get();

        // Top Doctor Revenue Attribution
        $topDoctors = Bill::query()
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->where(function ($dq) {
                $dq->whereNotNull('consultation_id')->orWhereNotNull('admission_id');
            })
            ->with(['consultation.doctor', 'admission.doctor'])
            ->get()
            ->groupBy(function ($bill) {
                $doctor = optional($bill->consultation)->doctor ?? optional($bill->admission)->doctor;
                return $doctor ? $doctor->full_name : 'Direct Billing';
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => (float) $group->sum('total_amount'),
                    'paid' => (float) $group->sum('paid_amount'),
                ];
            })
            ->sortByDesc('paid')
            ->take(5);

        // Daily Net Trend
        $dailyTrendRaw = (clone $paymentsQuery)
            ->select(
                DB::raw('DATE(received_at) as date'),
                DB::raw('SUM(CASE WHEN type = "payment" THEN amount ELSE -amount END) as net'),
                DB::raw('COUNT(*) as tx_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyTrend = [];
        foreach ($dailyTrendRaw as $row) {
            $dailyTrend[] = [
                'date' => $row->date,
                'net' => (float) $row->net,
                'patients' => (int) $row->tx_count,
            ];
        }

        $stats = [
            'summary' => [
                'gross_collection' => $grossCollection,
                'total_refunds' => $totalRefunds,
                'net_collection' => $netCollection,
                'total_transactions' => $totalTransactions,
                'total_invoiced' => $totalInvoiced,
                'total_discounts' => $totalDiscounts,
                'total_dues' => $totalDuesInPeriod,
                'unique_patients' => $uniquePatientsCount,
                'realization_rate' => $realizationRate,
                'arpp' => $arpp,
                'daily_velocity' => $dailyVelocity,
                'discount_leakage' => $discountLeakage,
                'days_count' => $daysCount,
            ],
            'method_breakdown' => $methodBreakdown,
            'revenue_by_type' => $revenueByType,
            'service_line' => $serviceLineBreakdown,
            'daily_trend' => $dailyTrend,
            'staff_collections' => $staffCollections,
            'top_doctors' => $topDoctors,
        ];

        $payments = (clone $paymentsQuery)->latest('received_at')->paginate(15);

        return view('livewire.reports.revenue-report', [
            'stats' => $stats,
            'payments' => $payments,
            'paymentMethods' => ['Cash', 'UPI', 'Card', 'Cheque', 'Insurance'],
        ]);
    }
}


