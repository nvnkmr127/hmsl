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
    public $bloodGroupFilter = '';
    public $sortBy = 'latest';

    protected $queryString = [
        'search' => ['except' => ''],
        'genderFilter' => ['except' => ''],
        'bloodGroupFilter' => ['except' => ''],
        'sortBy' => ['except' => 'latest'],
    ];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedGenderFilter() { $this->resetPage(); }
    public function updatedBloodGroupFilter() { $this->resetPage(); }
    public function updatedSortBy() { $this->resetPage(); }

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

    public function downloadExport(PatientService $service)
    {
        $patients = $service->getAll($this->search, [
            'gender' => $this->genderFilter,
            'blood_group' => $this->bloodGroupFilter,
        ], $this->sortBy);

        $filename = "hms-patients-" . now()->format('Y-m-d-His') . ".csv";
        
        return response()->streamDownload(function () use ($patients) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['UHID', 'Name', 'Gender', 'DOB', 'Phone', 'Blood Group', 'Address']);

            foreach ($patients as $p) {
                fputcsv($handle, [$p->uhid, $p->full_name, $p->gender, $p->date_of_birth?->format('Y-m-d'), $p->phone, $p->blood_group, $p->address]);
            }
            fclose($handle);
        }, $filename);
    }

    public function render(PatientService $service)
    {
        return view('livewire.counter.patient-list', [
            'patients' => $service->getAll($this->search, [
                'gender' => $this->genderFilter,
                'blood_group' => $this->bloodGroupFilter,
            ], $this->sortBy),
            'stats' => $service->getStats(),
            'bloodGroups' => Patient::whereNotNull('blood_group')->distinct()->pluck('blood_group'),
        ]);
    }
}
