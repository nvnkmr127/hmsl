<?php

namespace App\Livewire\Pharmacy;

use App\Models\Prescription;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PharmacyOrders extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $status = 'pending'; // pending, dispensed, all

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function dispense($id)
    {
        $this->validate([
            'status' => 'required|in:pending,dispensed,all',
        ]);

        try {
            $prescription = Prescription::findOrFail($id);

            if ($prescription->is_dispensed) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'This prescription is already dispensed.',
                ]);

                return;
            }

            $prescription->update([
                'is_dispensed' => true,
                'dispensed_at' => now(),
                'dispensed_by' => auth()->id(),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Prescription dispensed successfully!'
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Unable to mark this prescription as dispensed. Please try again.',
            ]);
        }
    }

    public function render()
    {
        $query = Prescription::with(['patient', 'doctor']);

        if ($this->status === 'pending') {
            $query->where('is_dispensed', false);
        } elseif ($this->status === 'dispensed') {
            $query->where('is_dispensed', true);
        }

        if ($this->search) {
            $query->whereHas('patient', function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('uhid', 'like', '%' . $this->search . '%');
            });
        }

        $prescriptions = $query->latest()->paginate(10)->withQueryString();

        return view('livewire.pharmacy.pharmacy-orders', [
            'prescriptions' => $prescriptions
        ]);
    }
}
