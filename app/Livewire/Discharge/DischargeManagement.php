<?php

namespace App\Livewire\Discharge;

use App\Models\Admission;
use App\Services\IpdService;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class DischargeManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $dischargeNotes = '';
    public $selectedAdmissionId;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectForDischarge($id)
    {
        $this->selectedAdmissionId = $id;
        $this->dischargeNotes = '';
        $this->dispatch('open-modal', name: 'discharge-modal');
    }

    public function processDischarge(IpdService $manager)
    {
        $this->validate([
            'selectedAdmissionId' => 'required|integer|exists:admissions,id',
            'dischargeNotes' => 'nullable|string|max:2000',
        ]);

        try {
            $admission = Admission::findOrFail($this->selectedAdmissionId);

            if ($admission->status !== 'Admitted') {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Only admitted patients can be discharged.',
                ]);

                return;
            }

            $manager->dischargePatient($admission, $this->dischargeNotes);

            $this->dispatch('close-modal', name: 'discharge-modal');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Patient has been discharged successfully!'
            ]);

            $this->reset(['selectedAdmissionId', 'dischargeNotes']);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Discharge failed. Please retry in a moment.',
            ]);
        }
    }

    public function render()
    {
        $query = Admission::with(['patient', 'bed.ward', 'doctor'])
            ->where('status', 'Admitted');

        if ($this->search) {
            $query->whereHas('patient', function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('uhid', 'like', '%' . $this->search . '%');
            });
        }

        $admissions = $query->latest('admission_date')->paginate(10);

        return view('livewire.discharge.discharge-management', [
            'admissions' => $admissions
        ]);
    }
}
