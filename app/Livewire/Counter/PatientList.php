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
    public $genderFilter = '';
    public $sortBy = 'latest';
    public $viewRecycleBin = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'genderFilter' => ['except' => ''],
        'sortBy' => ['except' => 'latest'],
        'viewRecycleBin' => ['except' => false],
    ];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedGenderFilter() { $this->resetPage(); }
    public function updatedSortBy() { $this->resetPage(); }
    public function updatedViewRecycleBin() { $this->resetPage(); }

    #[On('patient-saved'), On('booking-completed')]
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
            'message' => 'Patient record moved to Recycle Bin.'
        ]);
    }

    public function restorePatient($id, PatientService $service)
    {
        $service->restore($id);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Patient record restored successfully!'
        ]);
        
        $this->resetPage();
    }

    public function downloadExport(PatientService $service)
    {
        $patients = $service->getAll($this->search, [
            'gender' => $this->genderFilter,
        ], $this->sortBy, $this->viewRecycleBin);

        $filename = "hms-patients-" . now()->format('Y-m-d-His') . ".csv";
        
        return response()->streamDownload(function () use ($patients) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['UHID', 'Name', 'Gender', 'DOB', 'Phone', 'Address']);

            foreach ($patients as $p) {
                fputcsv($handle, [$p->uhid, $p->full_name, $p->gender, $p->date_of_birth?->format('Y-m-d'), $p->phone, $p->address]);
            }
            fclose($handle);
        }, $filename);
    }

    public function render(PatientService $service)
    {
        return view('livewire.counter.patient-list', [
            'patients' => $service->getAll($this->search, [
                'gender' => $this->genderFilter,
            ], $this->sortBy, $this->viewRecycleBin),
            'stats' => $service->getStats(),
        ]);
    }
}
