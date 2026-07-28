<?php

namespace App\Livewire\Reports;

use App\Models\BillDiscount;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountAuditReport extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $fromDate = '';
    public $toDate = '';
    public $doctorId = '';
    public $departmentId = '';
    public $isDashboard = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'departmentId' => ['except' => ''],
    ];

    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilter', 'fromDate', 'toDate', 'doctorId', 'departmentId'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'fromDate', 'toDate', 'doctorId', 'departmentId']);
        $this->resetPage();
    }

    public function approve($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'super_admin']) &&
            !Doctor::where('user_id', $user->id)->exists()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized action.']);
            return;
        }

        $discount = BillDiscount::findOrFail($id);
        $service = app(\App\Services\BillingService::class);
        $service->approveDiscount($discount);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount approved!']);
    }

    public function reject($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'super_admin']) &&
            !Doctor::where('user_id', $user->id)->exists()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized action.']);
            return;
        }

        $discount = BillDiscount::findOrFail($id);
        $service = app(\App\Services\BillingService::class);
        $service->rejectDiscount($discount);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount rejected.']);
    }

    protected function getDiscountsQuery()
    {
        return BillDiscount::with(['bill.patient', 'appliedBy', 'approver', 'doctor.department', 'bill.consultation.doctor.department'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('bill', function ($bq) {
                        $bq->where('bill_number', 'like', "%{$this->search}%")
                            ->orWhereHas('patient', function ($pq) {
                                $pq->where('full_name', 'like', "%{$this->search}%")
                                    ->orWhere('uhid', 'like', "%{$this->search}%");
                            });
                    })->orWhere('reason', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->fromDate, fn($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->when($this->doctorId, function ($q) {
                $q->where(function ($dq) {
                    $dq->where('doctor_id', $this->doctorId)
                        ->orWhereHas('bill.consultation', fn($cq) => $cq->where('doctor_id', $this->doctorId));
                });
            })
            ->when($this->departmentId, function ($q) {
                $q->where(function ($dq) {
                    $dq->whereHas('doctor', fn($docq) => $docq->where('department_id', $this->departmentId))
                        ->orWhereHas('bill.consultation.doctor', fn($cdocq) => $cdocq->where('department_id', $this->departmentId));
                });
            });
    }

    public function exportCsv()
    {
        $query = $this->getDiscountsQuery();
        $discounts = $query->latest()->get();

        return response()->streamDownload(function () use ($discounts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date', 'Bill No', 'Patient Name', 'UHID', 'Discount Amount (₹)',
                'Discount Type', 'Applied By', 'Approver / Doctor', 'Reason', 'Status'
            ]);

            foreach ($discounts as $d) {
                $approverName = $d->approver?->name ?? ($d->doctor?->full_name ?? 'Self / Pending');
                fputcsv($handle, [
                    $d->created_at ? $d->created_at->format('d M Y H:i') : 'N/A',
                    $d->bill?->bill_number ?? 'N/A',
                    $d->bill?->patient?->full_name ?? 'N/A',
                    $d->bill?->patient?->uhid ?? 'N/A',
                    $d->applied_amount,
                    $d->discount_type === 'percentage' ? $d->discount_value . '%' : 'Flat',
                    $d->appliedBy?->name ?? 'Unknown',
                    $approverName,
                    $d->reason,
                    $d->status
                ]);
            }
            fclose($handle);
        }, 'discount_audit_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function render()
    {
        $baseQuery = $this->getDiscountsQuery();
        $allDiscounts = (clone $baseQuery)->get();

        $totalDiscountAmount = (float) $allDiscounts->sum('applied_amount');
        $approvedAmount = (float) $allDiscounts->where('status', 'approved')->sum('applied_amount');
        $pendingAmount = (float) $allDiscounts->where('status', 'pending')->sum('applied_amount');
        $discountsCount = $allDiscounts->count();
        $approvedCount = $allDiscounts->where('status', 'approved')->count();
        $approvalRate = $discountsCount > 0 ? round(($approvedCount / $discountsCount) * 100, 1) : 0;

        $maxDiscount = $allDiscounts->sortByDesc('applied_amount')->first();
        $highestDiscountAmount = $maxDiscount ? (float) $maxDiscount->applied_amount : 0;
        $highestDiscountBill = $maxDiscount?->bill?->bill_number ?? 'N/A';

        $statusShare = [
            'Approved' => 0,
            'Pending' => 0,
            'Rejected' => 0,
        ];

        $dailyTrend = [];
        $departmentWise = [];
        $doctorWise = [];
        $authorizerCounts = [];

        foreach ($allDiscounts as $d) {
            if ($d->status === 'approved') {
                $statusShare['Approved'] += $d->applied_amount;
            } elseif ($d->status === 'pending') {
                $statusShare['Pending'] += $d->applied_amount;
            } else {
                $statusShare['Rejected'] += $d->applied_amount;
            }

            $dateKey = $d->created_at ? $d->created_at->format('Y-m-d') : 'Unknown';
            $dailyTrend[$dateKey] = ($dailyTrend[$dateKey] ?? 0) + $d->applied_amount;

            $deptName = $d->doctor?->department?->name 
                ?? ($d->bill?->consultation?->doctor?->department?->name ?? 'General / Other');
            $departmentWise[$deptName] = ($departmentWise[$deptName] ?? 0) + $d->applied_amount;

            $docName = $d->doctor?->full_name 
                ?? ($d->bill?->consultation?->doctor?->full_name ?? 'Unassigned');
            $doctorWise[$docName] = ($doctorWise[$docName] ?? 0) + $d->applied_amount;

            $authorizerName = $d->approver?->name ?? ($d->doctor?->full_name ?? 'Staff / Unassigned');
            $authorizerCounts[$authorizerName] = ($authorizerCounts[$authorizerName] ?? 0) + 1;
        }

        ksort($dailyTrend);
        arsort($authorizerCounts);
        arsort($departmentWise);

        $topAuthorizer = !empty($authorizerCounts) ? array_key_first($authorizerCounts) : 'None';
        $topDepartment = !empty($departmentWise) ? array_key_first($departmentWise) : 'General / Other';

        $stats = [
            'summary' => [
                'total_discount_amount' => $totalDiscountAmount,
                'approved_amount' => $approvedAmount,
                'pending_amount' => $pendingAmount,
                'discounts_count' => $discountsCount,
                'approval_rate' => $approvalRate,
                'highest_amount' => $highestDiscountAmount,
                'highest_bill' => $highestDiscountBill,
                'top_authorizer' => $topAuthorizer,
                'top_department' => $topDepartment,
            ],
            'status_share' => $statusShare,
            'daily_trend' => $dailyTrend,
            'department_wise' => $departmentWise,
            'doctor_wise' => $doctorWise,
        ];

        $discounts = (clone $baseQuery)->latest()->paginate(20);

        return view('livewire.reports.discount-audit-report', [
            'discounts' => $discounts,
            'stats' => $stats,
            'doctors' => Doctor::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }
}
