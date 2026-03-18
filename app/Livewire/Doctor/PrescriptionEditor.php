<?php

namespace App\Livewire\Doctor;

use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class PrescriptionEditor extends Component
{
    public $consultationId;
    public $patientName;
    public $doctorId;

    public $chief_complaint = '';
    public $diagnosis = '';
    public $advice = '';
    public $follow_up_date = '';

    public $medicines = [];
    public $existingPrescription = null;

    #[On('open-prescription')]
    public function open(int $consultationId)
    {
        $consultation = Consultation::with(['patient', 'doctor'])->findOrFail($consultationId);
        $this->consultationId = $consultationId;
        $this->patientName    = $consultation->patient->full_name;
        $this->doctorId       = $consultation->doctor_id;

        // Load existing if any
        $existing = Prescription::where('consultation_id', $consultationId)->first();
        if ($existing) {
            $this->existingPrescription = $existing->id;
            $this->chief_complaint = $existing->chief_complaint;
            $this->diagnosis       = $existing->diagnosis;
            $this->advice          = $existing->advice;
            $this->follow_up_date  = $existing->follow_up_date?->format('Y-m-d');
            $this->medicines       = $existing->medicines ?? [];
        } else {
            $this->reset(['chief_complaint', 'diagnosis', 'advice', 'follow_up_date', 'existingPrescription']);
            $this->medicines = [];
        }

        $this->dispatch('open-modal', ['name' => 'prescription-modal']);
    }

    public function addMedicine()
    {
        $this->medicines[] = [
            'name'         => '',
            'dose'         => '',
            'frequency'    => 'Once a day',
            'duration'     => '5 days',
            'instructions' => '',
        ];
    }

    public function removeMedicine(int $index)
    {
        unset($this->medicines[$index]);
        $this->medicines = array_values($this->medicines);
    }

    public function save()
    {
        $this->validate([
            'chief_complaint' => 'nullable|string|max:1000',
            'diagnosis'       => 'nullable|string|max:1000',
            'advice'          => 'nullable|string|max:2000',
            'follow_up_date'  => 'nullable|date|after:today',
            'medicines'       => 'nullable|array',
            'medicines.*.name' => 'required_with:medicines|string|max:255',
        ]);

        $data = [
            'consultation_id' => $this->consultationId,
            'patient_id'      => Consultation::find($this->consultationId)->patient_id,
            'doctor_id'       => $this->doctorId,
            'created_by'      => Auth::id(),
            'chief_complaint' => $this->chief_complaint,
            'diagnosis'       => $this->diagnosis,
            'advice'          => $this->advice,
            'follow_up_date'  => $this->follow_up_date ?: null,
            'medicines'       => array_values($this->medicines),
        ];

        if ($this->existingPrescription) {
            $prescription = Prescription::find($this->existingPrescription);
            $prescription->update($data);
            $msg = 'Prescription updated.';
        } else {
            $prescription = Prescription::create($data);
            $msg = 'Prescription saved successfully.';
        }

        $this->dispatch('close-modal', ['name' => 'prescription-modal']);
        $this->dispatch('prescription-saved', $this->consultationId);
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);

        return $prescription;
    }

    public function sendEmail()
    {
        $prescription = $this->save();
        
        try {
            if (!$prescription->patient->email) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Patient has no email address.']);
                return;
            }

            app(\App\Services\CommunicationService::class)->sendPrescription($prescription);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Prescription emailed to patient!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function print(int $prescriptionId)
    {
        return redirect()->route('counter.prescriptions.print', $prescriptionId);
    }

    public function render()
    {
        $medicineList = Medicine::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'strength']);

        return view('livewire.doctor.prescription-editor', compact('medicineList'));
    }
}
