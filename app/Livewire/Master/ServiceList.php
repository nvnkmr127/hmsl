<?php

namespace App\Livewire\Master;

use App\Models\Service;
use App\Services\ServiceManager;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $departmentFilter = '';


    #[On('service-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function toggleActive($id, ServiceManager $manager)
    {
        $service = Service::findOrFail($id);
        $manager->toggleActive($service);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Service status updated!'
        ]);
    }

    public function deleteService($id, ServiceManager $manager)
    {
        $service = Service::findOrFail($id);
        $manager->delete($service);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Service deleted!'
        ]);
    }

    public function render()
    {
        $services = Service::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('category', 'like', '%' . $this->search . '%');
                });
            })

            ->when($this->categoryFilter, function($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->departmentFilter, function($query) {
                $query->where('department_id', $this->departmentFilter);
            })
            ->with('department')

            ->latest()
            ->paginate(10);

        $categories = Service::select('category')->distinct()->pluck('category');

        return view('livewire.master.service-list', [
            'services' => $services,
            'categories' => $categories,
            'departments' => \App\Models\Department::where('is_active', true)->get()
        ]);

    }
}
