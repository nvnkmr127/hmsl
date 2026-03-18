<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Services\PatientService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class PatientList extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    #[On('patient-saved')]
    public function refreshList()
    {
        $this->resetPage();
    }

    public function deletePatient($id, PatientService $service)
    {
        $patient = Patient::findOrFail($id);
        $service->delete($patient);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Patient record deleted successfully!'
        ]);
    }

    public function render(PatientService $service)
    {
        return view('livewire.counter.patient-list', [
            'patients' => $service->getAll($this->search)
        ]);
    }
}
