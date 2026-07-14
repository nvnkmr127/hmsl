<?php

namespace App\Livewire\Billing;

use Livewire\Component;
use App\Models\Bill;
use App\Services\BillingService;
use Livewire\Attributes\On;

class PaymentModal extends Component
{
    public ?int $selectedBillId = null;
    public string $paymentType = 'payment';
    public string $paymentMethod = 'Cash';
    public $paymentAmount = 0;
    public ?string $paymentReference = null;
    public ?string $paymentNotes = null;

    #[On('openPaymentModal')]
    public function openPaymentModal(int $billId)
    {
        $bill = Bill::with('payments')->findOrFail($billId);
        $this->selectedBillId = $bill->id;
        $this->paymentType = 'payment';
        $this->paymentMethod = $bill->payment_method ?: 'Cash';
        $this->paymentAmount = max(0, (float) $bill->balance_amount);
        $this->paymentReference = null;
        $this->paymentNotes = null;
        $this->dispatch('open-modal', name: 'standalone-billing-payment-modal');
    }

    public function submitPayment(BillingService $service)
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

        try {
            $service->recordPayment(
                $bill,
                $amount,
                $this->paymentMethod,
                $this->paymentType,
                $this->paymentReference ?: null,
                $this->paymentNotes ?: null
            );

            $bill->refresh();
            $service->recalculatePaymentStatus($bill);

            $this->dispatch('close-modal', name: 'standalone-billing-payment-modal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Payment saved.']);
            
            $this->dispatch('refresh'); // Tell other components to refresh
            
            $this->reset(['selectedBillId', 'paymentType', 'paymentMethod', 'paymentAmount', 'paymentReference', 'paymentNotes']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.billing.payment-modal');
    }
}
