<?php

namespace App\Livewire\Counter;

use App\Models\Admission;
use App\Services\IpdManager;
use Livewire\Component;
use Livewire\WithPagination;

class IpdAdmissions extends Component
{
    use WithPagination;

    public $search = '';
    public bool $showDischarged = false;
    public ?int $selectedAdmissionId = null;
    public string $dischargeNotes = '';

    public function dischargePatient($id)
    {
        $this->selectedAdmissionId = (int) $id;
        $this->dischargeNotes = '';
        $this->dispatch('open-modal', ['name' => 'ipd-discharge-modal']);
    }

    public function confirmDischarge(IpdManager $manager)
    {
        if (!$this->selectedAdmissionId) {
            return;
        }

        $admission = Admission::findOrFail($this->selectedAdmissionId);
        $manager->dischargePatient($admission, $this->dischargeNotes ?: null);

        $this->dispatch('close-modal', ['name' => 'ipd-discharge-modal']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Patient discharged successfully!']);
        $this->reset(['selectedAdmissionId', 'dischargeNotes']);
    }

    public function render()
    {
        $admissions = Admission::with(['patient', 'bed', 'bed.ward', 'doctor.user'])
            ->when(!$this->showDischarged, fn ($q) => $q->where('status', 'Admitted'))
            ->when($this->search, function ($query) {
                $term = "%{$this->search}%";
                $query->where(function ($q) use ($term) {
                    $q->where('admission_number', 'like', $term)
                        ->orWhereHas('patient', function ($pq) use ($term) {
                            $pq->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('uhid', 'like', $term);
                        });
                });
            })
            ->latest('admission_date')
            ->paginate(10);

        return view('livewire.counter.ipd-admissions', [
            'admissions' => $admissions
        ]);
    }
}
