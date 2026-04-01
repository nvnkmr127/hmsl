<?php

namespace App\Livewire\Master;

use App\Models\Department;
use App\Services\DepartmentService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentList extends Component
{
    use WithPagination;

    public $search = '';

    #[On('department-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function toggleActive($id, DepartmentService $service)
    {
        $department = Department::findOrFail($id);
        $service->toggleActive($department);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Department status updated!'
        ]);
    }

    public function deleteDepartment($id, DepartmentService $service)
    {
        $department = Department::findOrFail($id);
        $service->delete($department);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Department deleted!'
        ]);
    }

    public function render()
    {
        $departments = Department::where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);


        return view('livewire.master.department-list', [
            'departments' => $departments
        ]);
    }
}
