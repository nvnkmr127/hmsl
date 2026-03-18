<?php

namespace App\Livewire\Master;

use App\Models\Service;
use App\Services\ServiceManager;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ServiceForm extends Component
{
    public $isEditing = false;
    public $serviceId;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|string|max:100')]
    public $category;

    #[Validate('required|numeric|min:0')]
    public $price;

    #[Validate('nullable|string|max:1000')]
    public $description;

    public $is_active = true;

    #[On('edit-service')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->serviceId = $id;
        
        $service = Service::findOrFail($id);
        $this->name = $service->name;
        $this->category = $service->category;
        $this->price = $service->price;
        $this->description = $service->description;
        $this->is_active = $service->is_active;

        $this->dispatch('open-modal', ['name' => 'service-modal']);
    }

    #[On('create-service')]
    public function create()
    {
        $this->reset(['name', 'category', 'price', 'description', 'is_active', 'serviceId', 'isEditing']);
        $this->resetValidation();
        $this->dispatch('open-modal', ['name' => 'service-modal']);
    }

    public function save(ServiceManager $manager)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $service = Service::findOrFail($this->serviceId);
            $manager->update($service, $data);
        } else {
            $manager->create($data);
        }

        $this->dispatch('close-modal', ['name' => 'service-modal']);
        $this->dispatch('service-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Service updated!' : 'Service created!'
        ]);
    }

    public function render()
    {
        return view('livewire.master.service-form');
    }
}
