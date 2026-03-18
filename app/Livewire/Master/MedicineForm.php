<?php

namespace App\Livewire\Master;

use App\Models\Medicine;
use App\Services\MedicineManager;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MedicineForm extends Component
{
    public $isEditing = false;
    public $medicineId;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('nullable|string|max:255')]
    public $generic_name;

    #[Validate('required|string|max:100')]
    public $category;

    #[Validate('nullable|string|max:50')]
    public $strength;

    #[Validate('nullable|string|max:255')]
    public $manufacturer;

    #[Validate('required|numeric|min:0')]
    public $buying_price = 0;

    #[Validate('required|numeric|min:0')]
    public $selling_price;

    #[Validate('required|integer|min:0')]
    public $stock_quantity = 0;

    #[Validate('required|integer|min:0')]
    public $min_stock_level = 10;

    #[Validate('nullable|date')]
    public $expire_date;

    public $is_active = true;

    #[On('edit-medicine')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->medicineId = $id;
        
        $medicine = Medicine::findOrFail($id);
        $this->name = $medicine->name;
        $this->generic_name = $medicine->generic_name;
        $this->category = $medicine->category;
        $this->strength = $medicine->strength;
        $this->manufacturer = $medicine->manufacturer;
        $this->buying_price = $medicine->buying_price;
        $this->selling_price = $medicine->selling_price;
        $this->stock_quantity = $medicine->stock_quantity;
        $this->min_stock_level = $medicine->min_stock_level;
        $this->expire_date = $medicine->expire_date?->format('Y-m-d');
        $this->is_active = $medicine->is_active;

        $this->dispatch('open-modal', ['name' => 'medicine-modal']);
    }

    #[On('create-medicine')]
    public function create()
    {
        $this->reset(['name', 'generic_name', 'category', 'strength', 'manufacturer', 'buying_price', 'selling_price', 'stock_quantity', 'min_stock_level', 'expire_date', 'is_active', 'medicineId', 'isEditing']);
        $this->resetValidation();
        $this->dispatch('open-modal', ['name' => 'medicine-modal']);
    }

    public function save(MedicineManager $manager)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'generic_name' => $this->generic_name,
            'category' => $this->category,
            'strength' => $this->strength,
            'manufacturer' => $this->manufacturer,
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_level' => $this->min_stock_level,
            'expire_date' => $this->expire_date,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $medicine = Medicine::findOrFail($this->medicineId);
            $manager->update($medicine, $data);
        } else {
            $manager->create($data);
        }

        $this->dispatch('close-modal', ['name' => 'medicine-modal']);
        $this->dispatch('medicine-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Medicine details updated!' : 'Medicine added to stock!'
        ]);
    }

    public function render()
    {
        return view('livewire.master.medicine-form');
    }
}
