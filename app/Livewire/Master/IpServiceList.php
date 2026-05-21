<?php

namespace App\Livewire\Master;

use App\Models\IpService;
use Livewire\Component;
use Livewire\WithPagination;

class IpServiceList extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $serviceId;
    
    public $name = '';
    public $price = '';
    public $description = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-modal', name: 'ip-service-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->serviceId = $id;
        
        $service = IpService::findOrFail($id);
        $this->name = $service->name;
        $this->price = $service->price;
        $this->description = $service->description;
        $this->is_active = $service->is_active;
        
        $this->dispatch('open-modal', name: 'ip-service-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $service = IpService::findOrFail($this->serviceId);
            $service->update([
                'name' => $this->name,
                'price' => $this->price,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
        } else {
            IpService::create([
                'name' => $this->name,
                'price' => $this->price,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
        }

        $this->dispatch('close-modal', name: 'ip-service-modal');
        $this->resetForm();
    }

    public function toggleStatus($id)
    {
        $service = IpService::findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);
    }

    public function delete($id)
    {
        $service = IpService::findOrFail($id);
        $service->delete();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->price = '';
        $this->description = '';
        $this->is_active = true;
        $this->serviceId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $services = IpService::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.master.ip-service-list', compact('services'));
    }
}
