<?php

namespace App\Livewire\Counter;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Services\BillingService;
use Livewire\Component;
use Livewire\WithPagination;

class BillingList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $methodFilter = '';
    public ?int $selectedBillId = null;
    public string $paymentType = 'payment';
    public string $paymentMethod = 'Cash';
    public $paymentAmount = 0;
    public ?string $paymentReference = null;
    public ?string $paymentNotes = null;

    // Discount Properties
    public $discountType = 'flat';
    public $discountValue = 0;
    public $discountReason = '';
    public $discountItemId = null;
    public $isAuthorizedByDoctor = false;
    public $authorizedLimit = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'methodFilter' => ['except' => ''],
    ];

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

    public function openPaymentModal(int $billId): void
    {
        $bill = Bill::with('payments')->findOrFail($billId);
        $this->selectedBillId = $bill->id;
        $this->paymentType = 'payment';
        $this->paymentMethod = $bill->payment_method ?: 'Cash';
        $this->paymentAmount = max(0, (float) $bill->balance_amount);
        $this->paymentReference = null;
        $this->paymentNotes = null;
        $this->dispatch('open-modal', name: 'billing-payment-modal');
    }

    public function submitPayment(BillingService $service): void
    {
        $this->validate([
            'selectedBillId' => 'required|integer|exists:bills,id',
            'paymentType' => 'required|in:payment,refund',
            'paymentMethod' => 'nullable|string|max:50',
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentReference' => 'nullable|string|max:100',
            'paymentNotes' => 'nullable|string|max:2000',
        ]);

        $bill = Bill::with('payments')->findOrFail($this->selectedBillId);
        $amount = round((float) $this->paymentAmount, 2);

        if ($this->paymentType === 'refund' && $amount > (float) $bill->paid_amount) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Refund amount cannot be more than paid amount.']);
            return;
        }

        $service->recordPayment(
            $bill,
            $amount,
            $this->paymentMethod,
            $this->paymentType,
            $this->paymentReference ?: null,
            $this->paymentNotes ?: null
        );

        $service->recalculatePaymentStatus($bill);

        $this->dispatch('close-modal', name: 'billing-payment-modal');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Payment saved.']);
        $this->reset(['selectedBillId', 'paymentType', 'paymentMethod', 'paymentAmount', 'paymentReference', 'paymentNotes']);
    }

    public function openDiscountModal(int $billId): void
    {
        $bill = Bill::with(['consultation', 'admission'])->findOrFail($billId);
        $this->selectedBillId = $bill->id;
        $this->discountType = 'flat';
        $this->discountValue = 0;
        $this->discountReason = '';
        $this->discountItemId = null;

        $clinicalSource = $bill->consultation ?? $bill->admission;
        $this->isAuthorizedByDoctor = (bool) ($clinicalSource->is_discount_authorized ?? false);
        $this->authorizedLimit = (float) ($clinicalSource->authorized_discount_limit ?? 0);

        $this->dispatch('open-modal', name: 'bill-discount-modal');
    }

    public function submitDiscount(BillingService $service): void
    {
        $this->validate([
            'selectedBillId' => 'required|integer|exists:bills,id',
            'discountType' => 'required|in:percentage,flat',
            'discountValue' => 'required|numeric|min:0.01',
            'discountReason' => 'required|string|max:255',
            'discountItemId' => 'nullable|exists:bill_items,id',
        ]);

        try {
            $bill = Bill::findOrFail($this->selectedBillId);
            $service->applyDiscount($bill, [
                'type' => $this->discountType,
                'value' => $this->discountValue,
                'reason' => $this->discountReason,
                'bill_item_id' => $this->discountItemId ?: null,
            ]);

            $this->dispatch('close-modal', name: 'bill-discount-modal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount applied successfully!']);
            $this->reset(['selectedBillId', 'discountType', 'discountValue', 'discountReason', 'discountItemId']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $bills = Bill::with(['patient', 'consultation.doctor', 'payments', 'discounts'])
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

        $paidTotal = (float) BillPayment::where('type', 'payment')->sum('amount');
        $refundTotal = (float) BillPayment::where('type', 'refund')->sum('amount');
        $todayPaid = (float) BillPayment::where('type', 'payment')->whereDate('received_at', today())->sum('amount');
        $todayRefund = (float) BillPayment::where('type', 'refund')->whereDate('received_at', today())->sum('amount');

        $stats = [
            'total_paid'    => $paidTotal - $refundTotal,
            'total_unpaid'  => Bill::whereIn('payment_status', ['Unpaid', 'Partially Paid'])->count(),
            'today_revenue' => $todayPaid - $todayRefund,
        ];

        return view('livewire.counter.billing-list', compact('bills', 'stats'));
    }
}
