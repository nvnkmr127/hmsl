<?php

namespace App\Livewire\Master;

use App\Models\Medicine;
use App\Services\MedicineManager;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MedicineList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $lowStockOnly = false;

    #[On('medicine-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function toggleActive($id, MedicineManager $manager)
    {
        $medicine = Medicine::findOrFail($id);
        $manager->toggleActive($medicine);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Medicine status updated!'
        ]);
    }

    public function deleteMedicine($id, MedicineManager $manager)
    {
        $medicine = Medicine::findOrFail($id);
        $manager->delete($medicine);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Medicine removed from inventory!'
        ]);
    }

    public function render()
    {
        $medicines = Medicine::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('generic_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->lowStockOnly, function($query) {
                $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
            })
            ->latest()
            ->paginate(15);

        $categories = Medicine::select('category')->distinct()->pluck('category');

        return view('livewire.master.medicine-list', [
            'medicines' => $medicines,
            'categories' => $categories
        ]);
    }
}
