<?php

namespace App\Livewire\Pharmacy;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Services\MedicineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function dispense($id, MedicineService $medicineService)
    {
        $this->validate([
            'status' => 'required|in:pending,dispensed,all',
        ]);

        try {
            DB::transaction(function () use ($id, $medicineService) {
                $prescription = Prescription::query()->lockForUpdate()->findOrFail($id);

                if ($prescription->is_dispensed) {
                    throw new \RuntimeException('This prescription is already dispensed.');
                }

                // 1. BILLING CHECK
                $bill = $prescription->consultation?->bill ?? $prescription->admission?->finalBill;
                if ($bill && $bill->payment_status !== 'Paid') {
                    throw new \RuntimeException('Prescription cannot be dispensed until the bill is Paid.');
                }

                $items = is_array($prescription->medicines) ? $prescription->medicines : [];
                foreach ($items as $item) {
                    $medicineId = isset($item['medicine_id']) ? (int) $item['medicine_id'] : null;
                    $name = isset($item['name']) ? trim((string) $item['name']) : '';
                    $qty = isset($item['qty']) ? (int) $item['qty'] : 1;
                    $qty = max(1, $qty);

                    $medicine = null;
                    if ($medicineId) {
                        $medicine = Medicine::find($medicineId);
                    } elseif ($name !== '') {
                        $medicine = Medicine::query()
                            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
                            ->first();
                    }

                    // 2. STOCK & EXISTENCE CHECK
                    if (!$medicine) {
                        throw new \RuntimeException("Medicine '{$name}' not found in inventory Master. Please add it to stock before dispensing.");
                    }

                    $medicineService->adjustStock(
                        $medicine,
                        -1 * $qty,
                        'dispense',
                        Prescription::class,
                        (int) $prescription->id,
                        'Dispensed for prescription #' . $prescription->id
                    );
                }

                $prescription->update([
                    'is_dispensed' => true,
                    'dispensed_at' => now(),
                    'dispensed_by' => Auth::id(),
                ]);

                event(new \App\Events\Pharmacy\PrescriptionDispensed($prescription->load(['patient', 'doctor'])));
            });

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Prescription dispensed successfully!'
            ]);
        } catch (Throwable $e) {
            report($e);
            $message = $e instanceof \RuntimeException ? $e->getMessage() : 'Unable to mark this prescription as dispensed. Please try again.';
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $message,
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
