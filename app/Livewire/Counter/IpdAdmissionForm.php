<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Ward;
use App\Models\Bed;
use App\Models\Admission;
use App\Services\IpdService;
use App\Models\ClinicalTemplate;
use Livewire\Component;
use Livewire\Attributes\Validate;

class IpdAdmissionForm extends Component
{
    #[Validate('required|exists:patients,id')]
    public $patientId;
    public $patient;

    #[Validate('required|exists:doctors,id')]
    public $doctorId;

    #[Validate('required|exists:wards,id')]
    public $wardId;

    #[Validate('required|exists:beds,id')]
    public $bedId;

    #[Validate('required')]
    public $admissionDate;

    public $weight;
    public $height;

    public $reason;
    public $notes;

    public $guardianName;
    public $guardianPhone;
    public $guardianRelation;
    public $emergencyContact;
    public $isEmergency = false;

    public $searchPatient = '';
    public $stats = [];

    public function mount($patientId = null)
    {
        $this->admissionDate = now()->format('Y-m-d\TH:i');

        if ($patientId) {
            $this->patientId = $patientId;
            $this->patient = Patient::find($patientId);
        }

        $activeDoctors = Doctor::where('is_active', true)->get();
        if ($activeDoctors->count() === 1) {
            $this->doctorId = $activeDoctors->first()->id;
        }

        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = [
            'total_active' => Admission::where('status', 'Admitted')->count(),
            'total_today' => Admission::whereDate('admission_date', now())->count(),
            'beds_available' => Bed::where('is_available', true)->count(),
            'beds_total' => Bed::count(),
        ];
    }

    public function selectPatient($id)
    {
        $this->patient = Patient::findOrFail($id);
        $this->patientId = $id;
        $this->searchPatient = '';
    }

    public function updatedWardId()
    {
        $this->bedId = null;
    }

    public function getAvailableBedsProperty()
    {
        if (!$this->wardId) return collect();
        return Bed::where('ward_id', $this->wardId)
            ->where('is_available', true)
            ->get();
    }

    public function save(IpdService $service)
    {
        $this->validate();

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'bed_id' => $this->bedId,
            'admission_date' => $this->admissionDate,
            'reason_for_admission' => $this->reason,
            'notes' => $this->notes,
            'weight' => $this->weight,
            'height' => $this->height,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'emergency_contact' => $this->emergencyContact,
        ];

        if ($this->isEmergency) {
            $data['is_emergency'] = true;
            $data['reason_for_admission'] = 'EMERGENCY: ' . ($this->reason ?? 'Emergency admission');
        }

        $admission = $service->admitPatient($data);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Patient admitted successfully!']);
        return redirect()->route('counter.ipd.show', $admission->id);
    }

    public function render()
    {
        $patients = [];
        if (strlen($this->searchPatient) >= 2) {
            $patients = Patient::query()
                ->where('first_name', 'like', "%{$this->searchPatient}%")
                ->orWhere('last_name', 'like', "%{$this->searchPatient}%")
                ->orWhere('uhid', 'like', "%{$this->searchPatient}%")
                ->orWhere('phone', 'like', "%{$this->searchPatient}%")
                ->limit(6)
                ->get();
        }

        return view('livewire.counter.ipd-admission-form', [
            'patients' => $patients,
            'doctors' => Doctor::where('is_active', true)->with('user')->get(),
            'wards' => Ward::all(),
            'reasons' => ClinicalTemplate::where('type', 'reason')->get(),
            'clinicalNotes' => ClinicalTemplate::where('type', 'notes')->get(),
        ]);
    }
}
