<?php

namespace App\Livewire\Laboratory;

use App\Models\LabOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class LaboratoryOrders extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $status = 'Pending';
    public $selectedOrder;
    public $results = []; // array for result entry

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function selectForResults($id)
    {
        try {
            $this->selectedOrder = LabOrder::with(['patient', 'labTest.parameters'])->findOrFail($id);

            // 1. BILLING GATEKEEPER
            $bill = $this->selectedOrder->consultation?->bill ?? $this->selectedOrder->admission?->finalBill;
            if ($bill && $bill->payment_status !== 'Paid') {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Cannot record results. Linked bill has not been settled.',
                ]);
                return;
            }

            $this->results = [];

            foreach ($this->selectedOrder->labTest?->parameters ?? [] as $p) {
                $this->results[$p->id] = '';
            }

            $this->dispatch('open-modal', name: 'results-modal');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Unable to open this order. Please refresh and try again.',
            ]);
        }
    }

    public function submitResults()
    {
        $this->validate([
            'selectedOrder.id' => 'required|integer|exists:lab_orders,id',
            'results' => 'required|array|min:1',
            'results.*' => 'nullable|string|max:255',
        ]);

        $hasAnyResult = collect($this->results)
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->isNotEmpty();

        if (! $hasAnyResult) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Please enter at least one result value before saving.',
            ]);

            return;
        }

        try {
            DB::transaction(function () {
                $order = LabOrder::findOrFail($this->selectedOrder->id);
                $now = now();
                
                // Save to new structured table
                foreach ($this->results as $paramId => $value) {
                    if (trim((string) $value) === '') continue;

                    \App\Models\LabResultValue::updateOrCreate(
                        [
                            'lab_order_id' => $order->id,
                            'lab_parameter_id' => $paramId,
                        ],
                        [
                            'patient_id' => $order->patient_id,
                            'result_value' => $value,
                            'technician_id' => Auth::id(),
                            'captured_at' => $now,
                        ]
                    );
                }

                $order->update([
                    'status' => 'Completed', // Technician completed
                    'completed_at' => $now,
                    'technician_id' => Auth::id()
                ]);

                event(new \App\Events\Laboratory\LabOrderCompleted($order->load(['patient', 'doctor', 'labTest'])));
            });

            $this->dispatch('close-modal', name: 'results-modal');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Lab results recorded successfully!'
            ]);

            $this->reset(['selectedOrder', 'results']);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Unable to save lab results: ' . $e->getMessage(),
            ]);
        }
    }

    public function verifyOrder($id)
    {
        try {
            $order = LabOrder::findOrFail($id);
            
            if ($order->status !== 'Completed') {
                throw new \Exception('Only completed tests can be verified.');
            }

            $order->update([
                'status' => 'Verified',
                'verified_at' => now(),
                'verified_by' => Auth::id()
            ]);

            // Mark result values as verified too
            $order->resultValues()->update(['verified_by' => Auth::id()]);

            $this->dispatch('notify', ['type' => 'success', 'message' => 'Lab results verified successfully!']);
        } catch (Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = LabOrder::with(['patient', 'doctor', 'labTest']);

        if ($this->status !== 'All') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->whereHas('patient', function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('uhid', 'like', '%' . $this->search . '%');
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('livewire.laboratory.laboratory-orders', [
            'orders' => $orders
        ]);
    }
}
