<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Ward;
use App\Models\Bed;
use App\Services\IpdManager;
use Livewire\Component;
use Livewire\Attributes\Validate;

class IpdAdmissionForm extends Component
{
    public $patientId;
    public $patientName;
    
    #[Validate('required|exists:doctors,id')]
    public $doctorId;
    
    #[Validate('required|exists:wards,id')]
    public $wardId;
    
    #[Validate('required|exists:beds,id')]
    public $bedId;
    
    #[Validate('required')]
    public $admissionDate;
    
    public $reason;
    public $notes;

    public $searchPatient = '';

    public function mount()
    {
        $this->admissionDate = now()->format('Y-m-d\TH:i');
        
        // Auto-select doctor if only one active exists
        $activeDoctors = Doctor::where('is_active', true)->get();
        if ($activeDoctors->count() === 1) {
            $this->doctorId = $activeDoctors->first()->id;
        }
    }

    public function selectPatient($id)
    {
        $patient = Patient::findOrFail($id);
        $this->patientId = $id;
        $this->patientName = $patient->full_name;
        $this->searchPatient = '';
    }

    public function getAvailableBedsProperty()
    {
        if (!$this->wardId) return collect();
        return Bed::where(fn($q) => $q->where('ward_id', '=', $this->wardId))
            ->where(fn($q) => $q->where('is_available', '=', true))
            ->get();
    }

    public function save(IpdManager $manager)
    {
        $this->validate();

        $manager->admitPatient([
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'bed_id' => $this->bedId,
            'admission_date' => $this->admissionDate,
            'reason_for_admission' => $this->reason,
            'notes' => $this->notes,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Patient admitted successfully!']);
        return redirect()->route('counter.ipd.index');
    }

    public function render()
    {
        $patients = [];
        if (strlen($this->searchPatient) >= 3) {
            $patients = Patient::query()->where(fn($q) => $q->where('first_name', 'like', "%{$this->searchPatient}%"))
                ->orWhere(fn($q) => $q->where('uhid', 'like', "%{$this->searchPatient}%"))
                ->limit(5)
                ->get();
        }

        return view('livewire.counter.ipd-admission-form', [
            'patients' => $patients,
            'doctors' => Doctor::where('is_active', true)->get(),
            'wards' => Ward::all(),
        ]);
    }
}
