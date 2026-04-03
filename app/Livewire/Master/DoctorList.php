<?php

namespace App\Livewire\Master;

use App\Models\Doctor;
use App\Models\Department;
use App\Services\DoctorService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorList extends Component
{
    use WithPagination;

    public $search = '';
    public $departmentFilter = '';
    public $showInactive = false;

    #[On('doctor-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function toggleActive($id, DoctorService $service)
    {
        $doctor = Doctor::findOrFail($id);
        $service->toggleActive($doctor);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Doctor status updated!'
        ]);
    }

    public function deleteDoctor($id, DoctorService $service)
    {
        $doctor = Doctor::findOrFail($id);
        $service->delete($doctor);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Doctor record deleted!'
        ]);
    }

    public function render()
    {
        $doctors = Doctor::with('department')
            ->when(!$this->showInactive, fn($q) => $q->active())
            ->when($this->search, fn($q) => $q->search($this->search))
            ->inDepartment($this->departmentFilter)
            ->latest()
            ->paginate(10);

        $departments = Department::orderBy('name')->get();

        return view('livewire.master.doctor-list', [
            'doctors' => $doctors,
            'departments' => $departments
        ]);
    }
}
