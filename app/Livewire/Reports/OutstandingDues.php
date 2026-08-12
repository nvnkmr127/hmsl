<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class OutstandingDues extends Component
{
    use WithPagination;

    public $from = '';
    public $to = '';
    public $search = '';
    public $doctorId = '';
    public $departmentId = '';

    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'search' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'departmentId' => ['except' => ''],
    ];

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'search', 'doctorId', 'departmentId'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['from', 'to', 'search', 'doctorId', 'departmentId']);
        $this->resetPage();
    }

    protected function getDuesQuery()
    {
        return Bill::with(['patient', 'consultation.doctor.department'])
            ->where('balance_amount', '>', 0)
            ->where('payment_status', '!=', 'Cancelled')
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('patient', fn($p) => $p->where('full_name', 'like', "%{$this->search}%")->orWhere('uhid', 'like', "%{$this->search}%"))
                      ->orWhere('bill_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->doctorId, function ($q) {
                $q->whereHas('consultation', fn($cq) => $cq->where('doctor_id', $this->doctorId));
            })
            ->when($this->departmentId, function ($q) {
                $q->whereHas('consultation.doctor', fn($dq) => $dq->where('department_id', $this->departmentId));
            });
    }

    public function exportCsv()
    {
        $query = $this->getDuesQuery();
        $dues = $query->get();

        return response()->streamDownload(function () use ($dues) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Bill No', 'Date', 'Patient Name', 'UHID', 'Phone',
                'Doctor', 'Department', 'Total Amount (₹)', 'Paid Amount (₹)',
                'Balance Due (₹)', 'Age (Days)', 'Payment Status'
            ]);

            foreach ($dues as $bill) {
                $days = $bill->created_at ? $bill->created_at->diffInDays(now()) : 0;
                fputcsv($handle, [
                    $bill->bill_number ?? 'N/A',
                    $bill->created_at ? $bill->created_at->format('d M Y') : 'N/A',
                    $bill->patient?->full_name ?? 'N/A',
                    $bill->patient?->uhid ?? 'N/A',
                    $bill->patient?->phone ?? 'N/A',
                    $bill->consultation?->doctor?->full_name ?? 'N/A',
                    $bill->consultation?->doctor?->department?->name ?? 'N/A',
                    $bill->total_amount,
                    $bill->paid_amount,
                    $bill->balance_amount,
                    $days,
                    $bill->payment_status
                ]);
            }
            fclose($handle);
        }, 'outstanding_dues_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function render()
    {
        $baseQuery = $this->getDuesQuery();
        $allDues = (clone $baseQuery)->get();

        $totalOutstanding = (float) $allDues->sum('balance_amount');
        $totalBilled = (float) $allDues->sum('total_amount');
        $totalPaid = (float) $allDues->sum('paid_amount');
        $patientsCount = $allDues->pluck('patient_id')->unique()->count();
        $avgDue = $patientsCount > 0 ? round($totalOutstanding / $patientsCount, 2) : 0;
        $collectionRate = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0;

        $aging = [
            '0-7 Days' => 0,
            '8-30 Days' => 0,
            '31-90 Days' => 0,
            '90+ Days' => 0,
        ];
        $agingAmounts = [
            '0-7 Days' => 0,
            '8-30 Days' => 0,
            '31-90 Days' => 0,
            '90+ Days' => 0,
        ];

        $departmentWise = [];
        $doctorWise = [];

        foreach ($allDues as $bill) {
            $days = $bill->created_at ? $bill->created_at->diffInDays(now()) : 0;
            if ($days <= 7) {
                $aging['0-7 Days']++;
                $agingAmounts['0-7 Days'] += $bill->balance_amount;
            } elseif ($days <= 30) {
                $aging['8-30 Days']++;
                $agingAmounts['8-30 Days'] += $bill->balance_amount;
            } elseif ($days <= 90) {
                $aging['31-90 Days']++;
                $agingAmounts['31-90 Days'] += $bill->balance_amount;
            } else {
                $aging['90+ Days']++;
                $agingAmounts['90+ Days'] += $bill->balance_amount;
            }

            $deptName = $bill->consultation?->doctor?->department?->name ?? 'General / Other';
            $departmentWise[$deptName] = ($departmentWise[$deptName] ?? 0) + $bill->balance_amount;

            $docName = $bill->consultation?->doctor?->full_name ?? 'Unassigned';
            $doctorWise[$docName] = ($doctorWise[$docName] ?? 0) + $bill->balance_amount;
        }

        $oldestBill = (clone $baseQuery)->oldest('created_at')->first();
        $oldestDays = $oldestBill?->created_at ? (int) round($oldestBill->created_at->diffInDays(now())) : 0;

        $maxBill = $allDues->sortByDesc('balance_amount')->first();
        $highestDebtorName = $maxBill?->patient?->full_name ?? 'None';
        $highestDebtorAmount = $maxBill ? $maxBill->balance_amount : 0;
        $criticalCount = $aging['31-90 Days'] + $aging['90+ Days'];

        $stats = [
            'summary' => [
                'total_outstanding' => $totalOutstanding,
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'patients_count' => $patientsCount,
                'avg_due' => $avgDue,
                'collection_rate' => $collectionRate,
                'oldest_days' => $oldestDays,
                'highest_debtor_name' => $highestDebtorName,
                'highest_debtor_amount' => $highestDebtorAmount,
                'critical_count' => $criticalCount,
            ],
            'aging_counts' => $aging,
            'aging_amounts' => $agingAmounts,
            'department_wise' => $departmentWise,
            'doctor_wise' => $doctorWise,
        ];

        $dues = (clone $baseQuery)->latest()->paginate(15);

        return view('livewire.reports.outstanding-dues', [
            'dues' => $dues,
            'stats' => $stats,
            'doctors' => Doctor::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }
}
