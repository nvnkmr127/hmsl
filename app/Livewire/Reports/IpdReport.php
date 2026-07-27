<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admission;
use App\Models\Doctor;
use App\Models\Ward;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;

class IpdReport extends Component
{
    use WithPagination;

    public $from;
    public $to;
    public $search = '';
    public $doctorId = '';
    public $wardId = '';
    public $status = '';
    public $billingStatus = '';
    public $dateBasis = 'admission';

    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'search' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'wardId' => ['except' => ''],
        'status' => ['except' => ''],
        'billingStatus' => ['except' => ''],
        'dateBasis' => ['except' => 'admission'],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'search', 'doctorId', 'wardId', 'status', 'billingStatus', 'dateBasis'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'doctorId', 'wardId', 'status', 'billingStatus', 'dateBasis']);
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
        $this->resetPage();
    }

    public function exportCsv()
    {
        $query = $this->getAdmissionsQuery();
        $admissions = $query->get();

        return response()->streamDownload(function () use ($admissions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Admission No', 'Admission Date', 'Discharge Date', 'Days Admitted', 
                'Patient Name', 'UHID', 'Phone', 'Doctor', 'Ward', 'Bed', 
                'Status', 'Total Bill (₹)', 'Paid Amount (₹)', 'Due Amount (₹)', 'Payment Status'
            ]);

            foreach ($admissions as $admission) {
                $bill = $admission->finalBill;
                fputcsv($handle, [
                    $admission->admission_number,
                    $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : 'N/A',
                    $admission->discharge_date ? $admission->discharge_date->format('d M Y, h:i A') : 'N/A',
                    $admission->days_admitted,
                    $admission->patient ? $admission->patient->full_name : 'N/A',
                    $admission->patient ? $admission->patient->uhid : 'N/A',
                    $admission->patient ? $admission->patient->phone : 'N/A',
                    $admission->doctor ? $admission->doctor->full_name : 'N/A',
                    $admission->ward_name,
                    optional($admission->bed)->bed_number ?? 'N/A',
                    $admission->status,
                    $bill ? $bill->total_amount : 0,
                    $bill ? $bill->paid_amount : 0,
                    $bill ? max(0, $bill->balance_amount) : 0,
                    $bill ? ucfirst($bill->payment_status) : 'Not Billed'
                ]);
            }
            fclose($handle);
        }, 'ipd_report_' . now()->format('Ymd_His') . '.csv');
    }

    private function getAdmissionsQuery()
    {
        return Admission::query()
            ->with(['patient', 'doctor.department', 'bed.ward', 'finalBill.payments'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('admission_number', 'like', "%{$this->search}%")
                       ->orWhereHas('patient', function ($pq) {
                           $pq->where('first_name', 'like', "%{$this->search}%")
                              ->orWhere('last_name', 'like', "%{$this->search}%")
                              ->orWhere('uhid', 'like', "%{$this->search}%")
                              ->orWhere('phone', 'like', "%{$this->search}%");
                       });
                });
            })
            ->when($this->doctorId, fn($q) => $q->where('doctor_id', $this->doctorId))
            ->when($this->wardId, fn($q) => $q->whereHas('bed', fn($bq) => $bq->where('ward_id', $this->wardId)))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->billingStatus === 'Not Billed', fn($q) => $q->doesntHave('finalBill'))
            ->when(in_array($this->billingStatus, ['Paid', 'Unpaid', 'Partially Paid']), function ($q) {
                $q->whereHas('finalBill', fn($bq) => $bq->where('payment_status', $this->billingStatus));
            })
            ->when($this->from || $this->to, function ($q) {
                if ($this->dateBasis === 'discharge') {
                    if ($this->from) $q->whereDate('discharge_date', '>=', $this->from);
                    if ($this->to) $q->whereDate('discharge_date', '<=', $this->to);
                } elseif ($this->dateBasis === 'billing') {
                    $q->whereHas('finalBill', function ($bq) {
                        if ($this->from) $bq->whereDate('created_at', '>=', $this->from);
                        if ($this->to) $bq->whereDate('created_at', '<=', $this->to);
                    });
                } else {
                    if ($this->from) $q->whereDate('admission_date', '>=', $this->from);
                    if ($this->to) $q->whereDate('admission_date', '<=', $this->to);
                }
            });
    }

    public function render(ReportService $reportService)
    {
        $baseQuery = $this->getAdmissionsQuery();

        // Calculate summary metrics
        $totalAdmissions = (clone $baseQuery)->count();
        $activeAdmissions = (clone $baseQuery)->where('status', 'Admitted')->count();
        $dischargesCount = (clone $baseQuery)->where('status', 'Discharged')->count();

        // Financial totals linked to these admissions
        $admissionIds = (clone $baseQuery)->pluck('id');
        $ipBills = Bill::whereIn('admission_id', $admissionIds)->get();
        $totalInvoiced = (float) $ipBills->sum('total_amount');
        $totalCollected = (float) $ipBills->sum('paid_amount');
        $totalDue = (float) $ipBills->sum('balance_amount');

        // Charts data
        $dailyTrend = (clone $baseQuery)
            ->select(DB::raw('DATE(admission_date) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $wardDistribution = (clone $baseQuery)
            ->join('beds', 'admissions.bed_id', '=', 'beds.id')
            ->join('wards', 'beds.ward_id', '=', 'wards.id')
            ->select('wards.name', DB::raw('count(*) as count'))
            ->groupBy('wards.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        $stats = [
            'summary' => [
                'total_admissions' => $totalAdmissions,
                'active_admissions' => $activeAdmissions,
                'discharges' => $dischargesCount,
                'total_invoiced' => $totalInvoiced,
                'total_collected' => $totalCollected,
                'total_due' => $totalDue,
            ],
            'daily_trend' => $dailyTrend,
            'ward_distribution' => $wardDistribution,
        ];

        $admissions = (clone $baseQuery)->latest('admission_date')->paginate(15);

        return view('livewire.reports.ipd-report', [
            'stats' => $stats,
            'admissions' => $admissions,
            'doctors' => Doctor::where('is_active', true)->get(),
            'wards' => Ward::where('is_active', true)->get(),
        ]);
    }
}

