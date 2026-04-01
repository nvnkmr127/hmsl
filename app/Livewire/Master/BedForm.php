<?php

namespace App\Livewire\Master;

use App\Models\Bed;
use App\Models\Ward;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BedForm extends Component
{
    public $isEditing = false;
    public $bedId;

    #[Validate('required|exists:wards,id')]
    public $ward_id;

    #[Validate('required|string|max:50')]
    public $bed_number;

    public $is_available = true;

    #[On('edit-bed')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->bedId = $id;
        
        $bed = Bed::findOrFail($id);
        $this->ward_id = $bed->ward_id;
        $this->bed_number = $bed->bed_number;
        $this->is_available = $bed->is_available;

        $this->dispatch('open-modal', name: 'bed-modal');
    }

    #[On('create-bed')]
    public function create()
    {
        $this->reset(['ward_id', 'bed_number', 'is_available', 'bedId', 'isEditing']);
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'bed-modal');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'ward_id' => $this->ward_id,
            'bed_number' => $this->bed_number,
            'is_available' => $this->is_available,
        ];

        if ($this->isEditing) {
            Bed::findOrFail($this->bedId)->update($data);
        } else {
            Bed::create($data);
        }

        $this->dispatch('close-modal', name: 'bed-modal');
        $this->dispatch('bed-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Bed updated!' : 'Bed registered!'
        ]);
    }

    public function render()
    {
        $wards = Ward::where('is_active', true)->get();
        return view('livewire.master.bed-form', compact('wards'));
    }
}
