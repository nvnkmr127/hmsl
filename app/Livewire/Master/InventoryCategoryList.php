<?php

namespace App\Livewire\Master;

use App\Models\InventoryCategory;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class InventoryCategoryList extends Component
{
    use WithPagination;

    public $search = '';

    #[On('category-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function deleteCategory($id)
    {
        $category = InventoryCategory::findOrFail($id);
        
        if ($category->items()->exists()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete category with items!'
            ]);
            return;
        }

        $category->delete();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Category deleted!'
        ]);
    }

    public function render()
    {
        $categories = InventoryCategory::withCount('items')
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.master.inventory-category-list', [
            'categories' => $categories
        ]);
    }
}
