<?php

namespace App\Livewire\Counter;

use App\Models\Admission;
use App\Models\LabTest;
use App\Services\LabOrderService;
use App\Services\IpdService;
use Livewire\Component;
use Livewire\WithPagination;

class IpdAdmissions extends Component
{
    use WithPagination;

    public $search = '';
    public bool $showDischarged = false;
    public ?int $selectedAdmissionId = null;
    public string $dischargeNotes = '';
    public ?int $selectedLabAdmissionId = null;
    public array $selectedLabTests = [];
    public ?string $labNotes = null;

    public function dischargePatient($id)
    {
        $this->selectedAdmissionId = (int) $id;
        $this->dischargeNotes = '';
        $this->dispatch('open-modal', name: 'ipd-discharge-modal');
    }

    public function orderLabs($id): void
    {
        $this->selectedLabAdmissionId = (int) $id;
        $this->selectedLabTests = [];
        $this->labNotes = null;
        $this->dispatch('open-modal', name: 'ipd-lab-order-modal');
    }

    public function confirmLabOrder(LabOrderService $service): void
    {
        if (!$this->selectedLabAdmissionId) {
            return;
        }

        $this->validate([
            'selectedLabAdmissionId' => 'required|integer|exists:admissions,id',
            'selectedLabTests' => 'required|array|min:1',
            'selectedLabTests.*' => 'integer|exists:lab_tests,id',
            'labNotes' => 'nullable|string|max:2000',
        ]);

        $admission = Admission::with(['patient', 'doctor'])->findOrFail($this->selectedLabAdmissionId);

        $created = $service->createOrders([
            'patient_id' => (int) $admission->patient_id,
            'doctor_id' => (int) $admission->doctor_id,
            'admission_id' => (int) $admission->id,
            'notes' => $this->labNotes,
        ], $this->selectedLabTests);

        if (count($created) === 0) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No lab orders were created.']);
            return;
        }

        $this->dispatch('close-modal', name: 'ipd-lab-order-modal');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Lab order created.']);
        $this->reset(['selectedLabAdmissionId', 'selectedLabTests', 'labNotes']);
    }

    public function confirmDischarge(IpdService $manager)
    {
        if (!$this->selectedAdmissionId) {
            return;
        }

        $admission = Admission::findOrFail($this->selectedAdmissionId);
        $manager->dischargePatient($admission, $this->dischargeNotes ?: null);

        $this->dispatch('close-modal', name: 'ipd-discharge-modal');
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
            'admissions' => $admissions,
            'dischargeTemplates' => \App\Models\ClinicalTemplate::where('type', 'discharge')->get(),
            'labTests' => LabTest::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }
}
