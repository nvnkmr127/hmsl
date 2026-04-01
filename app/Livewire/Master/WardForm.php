<?php

namespace App\Livewire\Master;

use App\Models\Ward;
use App\Services\WardManager;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class WardForm extends Component
{
    public $isEditing = false;
    public $wardId;

    #[Validate('nullable|string|max:50')]
    public $code;

    #[Validate('required|string|max:255')]

    public $name;

    #[Validate('required|string|max:100')]
    public $type;

    #[Validate('required|numeric|min:0')]
    public $daily_charge;

    #[Validate('required|integer|min:0')]
    public $capacity;

    public $is_active = true;

    #[On('edit-ward')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->wardId = $id;
        
        $ward = Ward::findOrFail($id);
        $this->code = $ward->code;
        $this->name = $ward->name;
        $this->type = $ward->type;
        $this->daily_charge = $ward->daily_charge;
        $this->capacity = $ward->capacity;
        $this->is_active = $ward->is_active;


        $this->dispatch('open-modal', name: 'ward-modal');
    }

    #[On('create-ward')]
    public function create()
    {
        $this->reset(['code', 'name', 'type', 'daily_charge', 'capacity', 'is_active', 'wardId', 'isEditing']);

        $this->resetValidation();
        $this->dispatch('open-modal', name: 'ward-modal');
    }

    public function save(WardManager $manager)
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'daily_charge' => $this->daily_charge,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
        ];


        if ($this->isEditing) {
            $ward = Ward::findOrFail($this->wardId);
            $manager->updateWard($ward, $data);
        } else {
            $manager->createWard($data);
        }

        $this->dispatch('close-modal', name: 'ward-modal');
        $this->dispatch('ward-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Ward updated!' : 'New ward created!'
        ]);
    }

    public function render()
    {
        return view('livewire.master.ward-form');
    }
}
