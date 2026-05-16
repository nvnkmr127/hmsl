<?php

namespace App\Livewire\Doctor;

use App\Models\Consultation;
use App\Models\LabTest;
use App\Services\LabOrderService;
use Livewire\Attributes\On;
use Livewire\Component;

class LabOrderComposer extends Component
{
    public ?int $consultationId = null;
    public ?int $admissionId = null;
    public ?int $patientId = null;
    public ?int $doctorId = null;
    public array $selectedTests = [];
    public ?string $notes = null;

    #[On('open-lab-order')]
    public function open(?int $consultationId = null, ?int $admissionId = null): void
    {
        if ($consultationId) {
            $consultation = Consultation::with(['patient', 'doctor'])->findOrFail($consultationId);
            $this->consultationId = $consultationId;
            $this->admissionId = null;
            $this->patientId = (int) $consultation->patient_id;
            $this->doctorId = (int) $consultation->doctor_id;
        } elseif ($admissionId) {
            $admission = \App\Models\Admission::with(['patient', 'doctor'])->findOrFail($admissionId);
            $this->admissionId = $admissionId;
            $this->consultationId = null;
            $this->patientId = (int) $admission->patient_id;
            $this->doctorId = (int) $admission->doctor_id;
        }

        $this->selectedTests = [];
        $this->notes = null;

        $this->dispatch('open-modal', name: 'lab-order-modal');
    }

    public function save(LabOrderService $service): void
    {
        $this->validate([
            'consultationId' => 'nullable|integer|exists:consultations,id',
            'admissionId' => 'nullable|integer|exists:admissions,id',
            'patientId' => 'required|integer|exists:patients,id',
            'doctorId' => 'required|integer|exists:doctors,id',
            'selectedTests' => 'required|array|min:1',
            'selectedTests.*' => 'integer|exists:lab_tests,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!$this->consultationId && !$this->admissionId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Either Consultation or Admission is required.']);
            return;
        }

        $created = $service->createOrders([
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'consultation_id' => $this->consultationId,
            'admission_id' => $this->admissionId,
            'notes' => $this->notes,
        ], $this->selectedTests);

        if (count($created) === 0) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No lab orders were created.']);
            return;
        }

        $this->dispatch('close-modal', name: 'lab-order-modal');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Lab order created.']);
        $this->dispatch('lab-order-created');
        $this->reset(['consultationId', 'admissionId', 'patientId', 'doctorId', 'selectedTests', 'notes']);
    }

    public function render()
    {
        $tests = LabTest::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        return view('livewire.doctor.lab-order-composer', [
            'tests' => $tests,
        ]);
    }
}

