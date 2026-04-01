<?php

namespace App\Livewire\Master;

use App\Models\InventoryCategory;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InventoryCategoryForm extends Component
{
    public $isEditing = false;
    public $categoryId;

    #[Validate('required|string|max:255|unique:inventory_categories,name')]
    public $name;

    #[Validate('nullable|string|max:1000')]
    public $description;

    #[On('edit-category')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->categoryId = $id;
        
        $category = InventoryCategory::findOrFail($id);
        $this->name = $category->name;
        $this->description = $category->description;

        $this->dispatch('open-modal', name: 'category-modal');
    }

    #[On('create-category')]
    public function create()
    {
        $this->reset(['name', 'description', 'categoryId', 'isEditing']);
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'category-modal');
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->validate(['name' => 'required|string|max:255|unique:inventory_categories,name,' . $this->categoryId]);
        } else {
            $this->validate();
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->isEditing) {
            InventoryCategory::findOrFail($this->categoryId)->update($data);
        } else {
            InventoryCategory::create($data);
        }

        $this->dispatch('close-modal', name: 'category-modal');
        $this->dispatch('category-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Category updated!' : 'Category created!'
        ]);
    }

    public function render()
    {
        return view('livewire.master.inventory-category-form');
    }
}
