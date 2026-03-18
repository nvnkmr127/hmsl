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

    public function dischargePatient($id, IpdManager $manager)
    {
        $admission = Admission::findOrFail($id);
        $manager->dischargePatient($admission);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Patient discharged successfully!']);
    }

    public function render()
    {
        $admissions = Admission::with(['patient', 'bed', 'bed.ward', 'doctor.user'])
            ->where(fn($q) => $q->where('admission_number', 'like', "%{$this->search}%")
                ->orWhereHas('patient', fn($pq) => $pq->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('uhid', 'like', "%{$this->search}%")
                ))
            ->latest('admission_date')
            ->paginate(10);

        return view('livewire.counter.ipd-admissions', [
            'admissions' => $admissions
        ]);
    }
}
