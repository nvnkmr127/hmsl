<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class InventoryManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $categoryFilter = 'All';
    public $showStockAdjustment = false;
    public $selectedItemId;
    public $adjustmentType = 'in';
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
        $this->selectedItemId = $id;
        $this->adjustmentType = 'in';
        $this->adjustmentQuantity = null;
        $this->adjustmentNotes = '';
        $this->dispatch('open-modal', name: 'stock-adjustment-modal');
    }

    public function submitAdjustment()
    {
        $this->validate([
            'selectedItemId' => 'required|integer|exists:inventory_items,id',
            'adjustmentType' => 'required|in:in,out,correction',
            'adjustmentQuantity' => 'required|numeric|gt:0',
            'adjustmentNotes' => 'nullable|string|max:500',
        ]);

        try {
            $item = InventoryItem::findOrFail($this->selectedItemId);

            if ($this->adjustmentType === 'out' && $item->stock_quantity < $this->adjustmentQuantity) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Outflow quantity is higher than available stock.',
                ]);

                return;
            }

            $delta = match ($this->adjustmentType) {
                'in' => (float) $this->adjustmentQuantity,
                'out' => -1 * (float) $this->adjustmentQuantity,
                default => 0,
            };

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type' => $this->adjustmentType,
                'quantity' => $this->adjustmentQuantity,
                'created_by' => auth()->id(),
                'notes' => $this->adjustmentNotes
            ]);

            if ($this->adjustmentType === 'correction') {
                $item->update(['stock_quantity' => $this->adjustmentQuantity]);
            } else {
                $item->increment('stock_quantity', $delta);
            }

            $this->dispatch('close-modal', name: 'stock-adjustment-modal');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock adjustment recorded successfully!'
            ]);

            $this->reset(['selectedItemId', 'adjustmentQuantity', 'adjustmentNotes']);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Stock update failed. Please try again.',
            ]);
        }
    }

    public function render()
    {
        $query = InventoryItem::with(['category', 'transactions']);

        if ($this->categoryFilter !== 'All') {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        $items = $query->latest()->paginate(12)->withQueryString();
        $categories = InventoryCategory::orderBy('name')->get();

        return view('livewire.inventory.inventory-manager', [
            'items' => $items,
            'categories' => $categories
        ]);
    }
}
