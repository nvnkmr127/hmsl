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
            ->when(!$this->showInactive, function($query) {
                $query->where('is_active', true);
            })
            ->when($this->search, function($query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('specialization', 'like', '%' . $this->search . '%');
            })
            ->when($this->departmentFilter, function($query) {
                $query->where('department_id', $this->departmentFilter);
            })
            ->latest()
            ->paginate(10);

        $departments = Department::orderBy('name')->get();

        return view('livewire.master.doctor-list', [
            'doctors' => $doctors,
            'departments' => $departments
        ]);
    }
}
