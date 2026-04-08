<?php

namespace App\Livewire\Pharmacy;

use App\Models\Medicine;
use App\Services\MedicineService;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PharmacyStock extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $categoryFilter = 'All';
    public $showExpiredOnly = false;
    public $showLowStockOnly = false;

    public $selectedMedicineId;
    public $adjustmentQuantity;
    public $adjustmentNotes;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function selectForStockAdjustment($id)
    {
        $this->selectedMedicineId = $id;
        $this->adjustmentQuantity = null;
        $this->adjustmentNotes = '';
        $this->dispatch('open-modal', name: 'stock-adjustment-modal');
    }

    public function submitAdjustment(MedicineService $service)
    {
        $this->validate([
            'selectedMedicineId' => 'required|exists:medicines,id',
            'adjustmentQuantity' => 'required|integer',
            'adjustmentNotes' => 'nullable|string|max:500',
        ]);

        try {
            $medicine = Medicine::findOrFail($this->selectedMedicineId);
            
            if ($this->adjustmentQuantity < 0 && $medicine->stock_quantity < abs($this->adjustmentQuantity)) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Reduction quantity is higher than available stock.',
                ]);
                return;
            }

            $service->adjustStock(
                $medicine,
                (int) $this->adjustmentQuantity,
                'adjustment',
                Medicine::class,
                (int) $medicine->id,
                $this->adjustmentNotes ?: null
            );

            $this->dispatch('close-modal', name: 'stock-adjustment-modal');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock updated successfully!'
            ]);

            $this->reset(['selectedMedicineId', 'adjustmentQuantity', 'adjustmentNotes']);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update stock. Please try again.',
            ]);
        }
    }

    public function render()
    {
        $query = Medicine::query();

        if ($this->categoryFilter !== 'All') {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('generic_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->showExpiredOnly) {
            $query->whereNotNull('expire_date')->where('expire_date', '<', now());
        }

        if ($this->showLowStockOnly) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
        }

        $medicines = $query->latest()->paginate(10)->withQueryString();
        $categories = Medicine::select('category')->distinct()->pluck('category');

        return view('livewire.pharmacy.pharmacy-stock', [
            'medicines' => $medicines,
            'categories' => $categories
        ]);
    }
}
