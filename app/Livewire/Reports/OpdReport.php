<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;

class OpdReport extends Component
{
    use WithPagination;

    public $from;
    public $to;
    public $search = '';
    public $doctorId = '';
    public $departmentId = '';
    public $visitType = '';
    public $status = '';
    public $billingStatus = '';
    public $dateBasis = 'visit'; // 'visit' or 'billing'

    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'search' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'departmentId' => ['except' => ''],
        'visitType' => ['except' => ''],
        'status' => ['except' => ''],
        'billingStatus' => ['except' => ''],
        'dateBasis' => ['except' => 'visit'],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'search', 'doctorId', 'departmentId', 'visitType', 'status', 'billingStatus', 'dateBasis'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'doctorId', 'departmentId', 'visitType', 'status', 'billingStatus', 'dateBasis']);
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
        $this->resetPage();
    }

    public function exportCsv()
    {
        $query = $this->getVisitsQuery();
        $visits = $query->get();

        return response()->streamDownload(function () use ($visits) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Token No', 'Date', 'Time', 'Patient Name', 'UHID', 'Phone',
                'Doctor', 'Department', 'Visit Type', 'Fee (₹)', 'Discount (₹)',
                'Bill No', 'Payment Status', 'Status'
            ]);

            foreach ($visits as $visit) {
                $bill = $visit->bill;
                fputcsv($handle, [
                    $visit->token_number ?? 'N/A',
                    $visit->consultation_date ? $visit->consultation_date->format('d M Y') : 'N/A',
                    $visit->consultation_time ? $visit->consultation_time->format('h:i A') : 'N/A',
                    $visit->patient ? $visit->patient->full_name : 'N/A',
                    $visit->patient ? $visit->patient->uhid : 'N/A',
                    $visit->patient ? $visit->patient->phone : 'N/A',
                    $visit->doctor ? $visit->doctor->full_name : 'N/A',
                    optional(optional($visit->doctor)->department)->name ?? 'N/A',
                    $visit->visit_type,
                    $visit->fee,
                    $visit->discount_amount ?? 0,
                    $bill ? $bill->bill_number : 'Not Billed',
                    $bill ? ucfirst($bill->payment_status) : 'Not Billed',
                    $visit->status,
                ]);
            }
            fclose($handle);
        }, 'opd_visits_report_' . now()->format('Ymd_His') . '.csv');
    }

    private function getVisitsQuery()
    {
        return Consultation::query()
            ->with(['patient', 'doctor.department', 'bill.payments'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('token_number', 'like', "%{$this->search}%")
                       ->orWhereHas('patient', function ($pq) {
                           $pq->where('first_name', 'like', "%{$this->search}%")
                              ->orWhere('last_name', 'like', "%{$this->search}%")
                              ->orWhere('uhid', 'like', "%{$this->search}%")
                              ->orWhere('phone', 'like', "%{$this->search}%");
                       });
                });
            })
            ->when($this->doctorId, fn($q) => $q->where('doctor_id', $this->doctorId))
            ->when($this->departmentId, fn($q) => $q->whereHas('doctor', fn($dq) => $dq->where('department_id', $this->departmentId)))
            ->when($this->visitType, function ($q) {
                if ($this->visitType === 'Follow-up') {
                    $q->whereIn('visit_type', ['Follow-up', 'Review']);
                } else {
                    $q->where('visit_type', $this->visitType);
                }
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->billingStatus === 'Not Billed', fn($q) => $q->doesntHave('bill'))
            ->when(in_array($this->billingStatus, ['Paid', 'Unpaid', 'Partially Paid']), function ($q) {
                $q->whereHas('bill', fn($bq) => $bq->where('payment_status', $this->billingStatus));
            })
            ->when($this->from || $this->to, function ($q) {
                if ($this->dateBasis === 'billing') {
                    $q->whereHas('bill', function ($bq) {
                        if ($this->from) $bq->whereDate('created_at', '>=', $this->from);
                        if ($this->to) $bq->whereDate('created_at', '<=', $this->to);
                    });
                } else {
                    if ($this->from) $q->whereDate('consultation_date', '>=', $this->from);
                    if ($this->to) $q->whereDate('consultation_date', '<=', $this->to);
                }
            });
    }

    public function render(ReportService $reportService)
    {
        $baseQuery = $this->getVisitsQuery();

        $totalVisits = (clone $baseQuery)->count();
        $newVisits = (clone $baseQuery)->where('visit_type', 'New')->count();
        $revisits = (clone $baseQuery)->whereIn('visit_type', ['Follow-up', 'Review'])->count();
        $revisitRate = $totalVisits > 0 ? round(($revisits / $totalVisits) * 100, 1) : 0;

        $totalFees = (float) (clone $baseQuery)->sum('fee');
        $totalDiscounts = (float) (clone $baseQuery)->sum('discount_amount');
        
        // Fee Collected from Bills
        $consultationIds = (clone $baseQuery)->pluck('id');
        $totalCollected = (float) Bill::whereIn('consultation_id', $consultationIds)->sum('paid_amount');

        // Doctor-wise share
        $doctorWise = (clone $baseQuery)
            ->with('doctor:id,full_name')
            ->select('doctor_id', DB::raw('count(*) as count'))
            ->groupBy('doctor_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->doctor->full_name ?? "Doc #{$item->doctor_id}" => $item->count])
            ->toArray();

        // Daily trend
        $dailyTrend = (clone $baseQuery)
            ->select(DB::raw('DATE(consultation_date) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Day-wise distribution
        $daysOrder = ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0];
        $dayWiseRaw = (clone $baseQuery)
            ->select(DB::raw('DATE_FORMAT(consultation_date, "%a") as day'), DB::raw('count(*) as count'))
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();
        $dayWise = array_merge($daysOrder, array_intersect_key($dayWiseRaw, $daysOrder));

        // Peak hours
        $peakHours = (clone $baseQuery)
            ->select(DB::raw('DATE_FORMAT(created_at, "%h %p") as hour_label'), DB::raw('HOUR(created_at) as h'), DB::raw('count(*) as count'))
            ->groupBy('hour_label', 'h')
            ->orderBy('h')
            ->pluck('count', 'hour_label')
            ->toArray();

        // Department-wise share
        $departmentWise = (clone $baseQuery)
            ->join('doctors', 'consultations.doctor_id', '=', 'doctors.id')
            ->join('departments', 'doctors.department_id', '=', 'departments.id')
            ->select('departments.name as department', DB::raw('count(consultations.id) as count'))
            ->groupBy('departments.name')
            ->pluck('count', 'department')
            ->toArray();

        $busiestDay = !empty(array_filter($dayWise)) ? array_search(max($dayWise), $dayWise) : 'N/A';
        $peakHour = !empty($peakHours) ? array_search(max($peakHours), $peakHours) : 'N/A';
        $avgDailyVisits = count($dailyTrend) > 0 ? round($totalVisits / count($dailyTrend), 1) : $totalVisits;
        $topDepartment = !empty($departmentWise) ? array_search(max($departmentWise), $departmentWise) : 'N/A';

        $stats = [
            'summary' => [
                'total_visits' => $totalVisits,
                'new_visits' => $newVisits,
                'revisits' => $revisits,
                'revisit_rate' => $revisitRate,
                'total_fees' => $totalFees,
                'total_discounts' => $totalDiscounts,
                'total_collected' => $totalCollected,
                'busiest_day' => $busiestDay,
                'peak_hour' => $peakHour,
                'avg_daily_visits' => $avgDailyVisits,
                'top_department' => $topDepartment,
            ],
            'doctor_wise' => $doctorWise,
            'daily_trend' => $dailyTrend,
            'day_wise' => $dayWise,
            'peak_hours' => $peakHours,
            'department_wise' => $departmentWise,
        ];

        $visits = (clone $baseQuery)->latest('consultation_date')->paginate(15);

        return view('livewire.reports.opd-report', [
            'stats' => $stats,
            'visits' => $visits,
            'doctors' => Doctor::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }
}

