<?php

namespace App\Livewire\Reports;

use App\Models\BillDiscount;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountAuditReport extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function approve($id, \App\Services\BillingService $service)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'super_admin']) && !\App\Models\Doctor::where('user_id', Auth::id())->exists()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized action.']);
            return;
        }

        $discount = BillDiscount::findOrFail($id);
        $service->approveDiscount($discount);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount approved!']);
    }

    public function reject($id, \App\Services\BillingService $service)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'super_admin']) && !\App\Models\Doctor::where('user_id', Auth::id())->exists()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized action.']);
            return;
        }

        $discount = BillDiscount::findOrFail($id);
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
            ->latest()
            ->paginate(20);

        return view('livewire.reports.discount-audit-report', [
            'discounts' => $discounts
        ]);
    }
}
