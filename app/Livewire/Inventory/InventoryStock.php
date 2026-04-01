<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryStock extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $typeFilter = 'All';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $transactions = InventoryTransaction::with(['item.category', 'creator'])
            ->when($this->search, function ($q) {
                $q->whereHas('item', fn($iq) => $iq->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->typeFilter !== 'All', function ($q) {
                $q->where('type', $this->typeFilter);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.inventory.inventory-stock', compact('transactions'));
    }
}
