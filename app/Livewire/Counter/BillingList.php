<?php

namespace App\Livewire\Counter;

use App\Models\Bill;
use Livewire\Component;
use Livewire\WithPagination;

class BillingList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $methodFilter = '';

    public function updatedSearch()    { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedMethodFilter() { $this->resetPage(); }

    public function sendEmail($billId)
    {
        $bill = Bill::findOrFail($billId);
        
        try {
            if (!$bill->patient->email) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Patient has no email address.']);
                return;
            }

            app(\App\Services\CommunicationService::class)->sendInvoice($bill);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Invoice emailed to patient!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $bills = Bill::with(['patient', 'consultation.doctor'])
            ->when($this->search, function ($q) {
                $q->where('bill_number', 'like', "%{$this->search}%")
                  ->orWhereHas('patient', fn($pq) =>
                      $pq->where('first_name', 'like', "%{$this->search}%")
                         ->orWhere('last_name', 'like', "%{$this->search}%")
                         ->orWhere('uhid', 'like', "%{$this->search}%")
                  );
            })
            ->when($this->statusFilter, fn($q) => $q->where('payment_status', $this->statusFilter))
            ->when($this->methodFilter, fn($q) => $q->where('payment_method', $this->methodFilter))
            ->latest()
            ->paginate(15);

        $stats = [
            'total_paid'    => Bill::where('payment_status', 'Paid')->sum('total_amount'),
            'total_unpaid'  => Bill::where('payment_status', 'Unpaid')->count(),
            'today_revenue' => Bill::where('payment_status', 'Paid')
                                   ->whereDate('created_at', today())
                                   ->sum('total_amount'),
        ];

        return view('livewire.counter.billing-list', compact('bills', 'stats'));
    }
}
