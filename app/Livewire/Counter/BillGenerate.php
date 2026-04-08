<?php

namespace App\Livewire\Counter;

use App\Models\Consultation;
use App\Models\Bill;
use App\Models\Patient;
use App\Services\BillingService;
use Livewire\Component;
use Livewire\Attributes\On;

class BillGenerate extends Component
{
    public $consultationId;
    public $patientId;
    public $patientName;
    public $items = [];
    public $discount = 0;
    public $tax = 0;
    public $paymentStatus = 'Paid';
    public $amountPaid = 0;
    public $paymentMethod = 'Cash';
    public $notes;

    #[On('generate-bill')]
    public function openBillingModal($data = null)
    {
        if (!$data) return;
        $this->consultationId = is_array($data) ? ($data['consultationId'] ?? null) : $data;
        $consultation = Consultation::with('patient', 'doctor')->findOrFail($this->consultationId);


        
        $this->patientId = $consultation->patient_id;
        $this->patientName = $consultation->patient->full_name;
        
        $doctorName = $consultation->doctor ? ' - Dr. ' . $consultation->doctor->full_name : '';
        $this->items = [
            [
                'name' => ($consultation->service?->name ?? 'Consultation Fee') . $doctorName,
                'type' => 'Consultation',
                'quantity' => 1,
                'unit_price' => $consultation->fee
            ]
        ];

        
        $this->discount = $consultation->discount_amount ?? 0;
        $this->tax = 0;
        $this->paymentStatus = $consultation->payment_status === 'Paid' ? 'Paid' : 'Unpaid';
        $this->paymentMethod = 'Cash';
        $this->amountPaid = $this->paymentStatus === 'Paid' ? (float) $this->total : 0;
        $this->notes = '';

        $this->dispatch('open-modal', name: 'billing-modal');

    }

    #[On('generate-bill-for-patient')]
    public function openBillingModalForPatient($data = null)
    {
        if (!$data) return;
        $this->consultationId = null;
        $this->patientId = is_array($data) ? ($data['patientId'] ?? null) : $data;
        $patient = Patient::findOrFail($this->patientId);



        $this->patientId = $patient->id;
        $this->patientName = $patient->full_name;

        $this->items = [
            ['name' => 'Service', 'type' => 'Other', 'quantity' => 1, 'unit_price' => 0],
        ];
        $this->discount = 0;
        $this->tax = 0;
        $this->paymentStatus = 'Paid';
        $this->paymentMethod = 'Cash';
        $this->amountPaid = (float) $this->total;
        $this->notes = '';

        $this->dispatch('open-modal', name: 'billing-modal');

    }

    public function addItem()
    {
        $this->items[] = ['name' => '', 'type' => 'Misc', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });
    }

    public function getTotalProperty()
    {
        return $this->subtotal - $this->discount + $this->tax;
    }

    public function save(BillingService $service)
    {
        $this->validate([
            'paymentStatus' => 'required|in:Paid,Unpaid,Partially Paid,Partial',
            'amountPaid' => 'nullable|numeric|min:0',
            'paymentMethod' => 'nullable|string|max:50',
        ]);

        $bill = $service->createBill([
            'patient_id' => $this->patientId,
            'consultation_id' => $this->consultationId,
            'discount_amount' => $this->discount,
            'tax_amount' => $this->tax,
            'payment_status' => $this->paymentStatus,
            'paid_amount' => (float) $this->amountPaid,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
        ], $this->items);

        // $service->markAsPaid($bill, $this->paymentMethod); // redundant since createBill sets it

        $this->dispatch('close-modal', name: 'billing-modal');

        $this->dispatch('bill-generated', billId: $bill->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Bill generated successfully!']);
    }

    public function render()
    {
        return view('livewire.counter.bill-generate');
    }
}
