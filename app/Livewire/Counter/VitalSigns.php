<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Consultation;
use App\Services\VitalService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class VitalSigns extends Component
{
    public $patientId;
    public $consultationId;
    public $patientName;

    #[Validate('nullable|numeric|min:0|max:500')]
    public $weight;

    #[Validate('nullable|numeric|min:0|max:300')]
    public $height;

    #[Validate('nullable|numeric|min:30|max:115')]
    public $temperature;

    #[Validate('nullable|integer|min:0|max:300')]
    public $pulse;

    #[Validate('nullable|string|max:10')]
    public $bp_systolic;

    #[Validate('nullable|string|max:10')]
    public $bp_diastolic;

    #[Validate('nullable|integer|min:0|max:100')]
    public $resp_rate;

    #[Validate('nullable|integer|min:0|max:100')]
    public $spo2;

    #[Validate('nullable|numeric|min:0')]
    public $blood_sugar;

    #[Validate('nullable|string')]
    public $notes;

    #[On('record-vitals')]
    public function openRecordModal($patientId, $consultationId = null)
    {
        $this->reset(['weight', 'height', 'temperature', 'pulse', 'bp_systolic', 'bp_diastolic', 'resp_rate', 'spo2', 'blood_sugar', 'notes']);
        $this->patientId = $patientId;
        $this->consultationId = $consultationId;
        
        $patient = Patient::findOrFail($patientId);
        $this->patientName = $patient->full_name;

        $this->dispatch('open-modal', ['name' => 'vitals-modal']);
    }

    public function save(VitalService $service)
    {
        $data = $this->validate();
        $data['patient_id'] = $this->patientId;
        $data['consultation_id'] = $this->consultationId;

        $service->record($data);

        $this->dispatch('close-modal', ['name' => 'vitals-modal']);
        $this->dispatch('vitals-recorded');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Vital signs recorded successfully!'
        ]);
    }

    public function render()
    {
        return view('livewire.counter.vital-signs');
    }
}
