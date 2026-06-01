<?php

namespace App\Livewire\Reports;

use App\Models\BillDiscount;
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
    public $isDashboard = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
    ];

    public function approve($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'super_admin']) && 
            !\App\Models\Doctor::where('user_id', $user->id)->exists() && 
            !\App\Models\HospitalOwner::isOwner($user)) {
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
            !\App\Models\Doctor::where('user_id', $user->id)->exists() && 
            !\App\Models\HospitalOwner::isOwner($user)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized action.']);
            return;
        }

        $discount = BillDiscount::findOrFail($id);
        $service = app(\App\Services\BillingService::class);
        $service->rejectDiscount($discount);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount rejected.']);
    }

    public function render()
    {
        $discounts = BillDiscount::with(['bill.patient', 'appliedBy', 'approver', 'doctor'])
            ->when($this->search, function ($q) {
                $q->whereHas('bill', function ($bq) {
                    $bq->where('bill_number', 'like', "%{$this->search}%")
                      ->orWhereHas('patient', function ($pq) {
                          $pq->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%");
                      });
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->fromDate, fn($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->latest()
            ->paginate(20);

        return view('livewire.reports.discount-audit-report', [
            'discounts' => $discounts
        ]);
    }
}
