<?php

namespace App\Livewire\Counter;

use App\Models\Admission;
use App\Models\Doctor;
use App\Models\Ward;
use App\Models\Bed;
use App\Models\ClinicalTemplate;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class IpdAdmissionEdit extends Component
{
    public ?Admission $admission = null;

    #[Validate('required|exists:doctors,id')]
    public $doctorId;

    #[Validate('required|exists:wards,id')]
    public $wardId;

    #[Validate('nullable|exists:beds,id')]
    public $bedId;

    #[Validate('required')]
    public $admissionDate;

    #[Validate('required|string|max:255')]
    public $manualAdmissionNumber;

    public $weight;
    public $height;
    public $pulse;
    public $bp_systolic;
    public $bp_diastolic;
    public $resp_rate;
    public $spo2;

    public $reason;
    public $notes;

    public $guardianName;
    public $guardianPhone;
    public $guardianRelation;
    public $emergencyContact;
    public $isEmergency = false;

    public function mount()
    {
        // Initial empty mount
    }

    #[\Livewire\Attributes\On('edit-admission')]
    public function loadAdmission($id)
    {
        $this->admission = Admission::with(['patient', 'bed', 'ipdVitals'])->findOrFail($id);

        $this->doctorId = $this->admission->doctor_id;
        
        $this->wardId = $this->admission->bed?->ward_id;
        $this->bedId = $this->admission->bed_id;
        
        $this->admissionDate = $this->admission->admission_date ? $this->admission->admission_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        
        $this->manualAdmissionNumber = $this->admission->admission_number;
        
        $this->weight = $this->admission->weight;
        $this->height = $this->admission->height;

        $firstVital = $this->admission->ipdVitals->first();
        if ($firstVital) {
            $this->pulse = $firstVital->pulse;
            $this->bp_systolic = $firstVital->bp_systolic;
            $this->bp_diastolic = $firstVital->bp_diastolic;
            $this->resp_rate = $firstVital->resp_rate;
            $this->spo2 = $firstVital->spo2;
        } else {
            $this->pulse = null;
            $this->bp_systolic = null;
            $this->bp_diastolic = null;
            $this->resp_rate = null;
            $this->spo2 = null;
        }

        $this->reason = $this->admission->reason_for_admission;
        $this->notes = $this->admission->notes;
        $this->guardianName = $this->admission->guardian_name;
        $this->guardianPhone = $this->admission->guardian_phone;
        $this->guardianRelation = $this->admission->guardian_relation;
        $this->emergencyContact = $this->admission->emergency_contact;
        $this->isEmergency = $this->admission->is_emergency;

        $this->dispatch('open-modal', name: 'edit-admission-modal');
    }

    public function updatedWardId()
    {
        $this->bedId = null;
    }

    #[Computed]
    public function availableBeds()
    {
        if (!$this->wardId) return collect();
        return Bed::where('ward_id', $this->wardId)
            ->where(function($q) {
                $q->where('is_available', true)
                  ->orWhere('id', $this->admission->bed_id);
            })
            ->get();
    }

    public function getAdmissionNumberPrefix()
    {
        if (!$this->wardId) {
            return 'ADM-';
        }
        $ward = Ward::find($this->wardId);
        $isNicu = $ward && strtoupper(trim($ward->code)) === 'NICU';
        return $isNicu ? 'ADM-NICU-' : 'ADM-';
    }

    public function save()
    {
        $this->validate();

        // Check if admission number is modified and already exists
        if ($this->manualAdmissionNumber !== $this->admission->admission_number) {
            if (Admission::where('admission_number', $this->manualAdmissionNumber)->where('id', '!=', $this->admission->id)->exists()) {
                $this->addError('manualAdmissionNumber', 'Admission number already exists');
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Admission number already exists']);
                return;
            }
        }

        $selectedWard = Ward::find($this->wardId);
        $isIcu = $selectedWard && in_array(strtoupper($selectedWard->name), ['NICU', 'PICU']);

        if (!$isIcu && !$this->bedId) {
            $this->addError('bedId', 'Please choose a bed.');
            return;
        }

        $finalBedId = $this->bedId;
        if ($isIcu && !$finalBedId) {
            $firstAvailable = Bed::where('ward_id', $this->wardId)->where('is_available', true)->first();
            if (!$firstAvailable && $this->admission->bed_id && $this->admission->bed->ward_id == $this->wardId) {
                $finalBedId = $this->admission->bed_id;
            } else if (!$firstAvailable) {
                $this->addError('wardId', 'No beds available in ' . $selectedWard->name);
                return;
            } else {
                $finalBedId = $firstAvailable->id;
            }
        }

        // Release old bed if changed
        if ($this->admission->bed_id && $this->admission->bed_id != $finalBedId) {
            $oldBed = Bed::find($this->admission->bed_id);
            if ($oldBed) {
                $oldBed->update(['is_available' => true]);
            }
            
            // Occupy new bed
            if ($finalBedId) {
                Bed::where('id', $finalBedId)->update(['is_available' => false]);
            }
        }

        $this->admission->update([
            'doctor_id' => $this->doctorId,
            'bed_id' => $finalBedId,
            'admission_date' => $this->admissionDate,
            'admission_number' => $this->manualAdmissionNumber,
            'reason_for_admission' => $this->reason,
            'notes' => $this->notes,
            'weight' => $this->weight,
            'height' => $this->height,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'emergency_contact' => $this->emergencyContact,
            'is_emergency' => $this->isEmergency,
        ]);

        $hasVitals = !empty($this->pulse) || !empty($this->bp_systolic) || !empty($this->bp_diastolic) || !empty($this->resp_rate) || !empty($this->spo2) || !empty($this->weight) || !empty($this->height);

        if ($hasVitals) {
            $vitalData = [
                'pulse' => $this->pulse,
                'bp_systolic' => $this->bp_systolic,
                'bp_diastolic' => $this->bp_diastolic,
                'resp_rate' => $this->resp_rate,
                'spo2' => $this->spo2,
                'weight' => $this->weight,
                'height' => $this->height,
            ];

            $firstVital = $this->admission->ipdVitals()->first();
            if ($firstVital) {
                $firstVital->update($vitalData);
            } else {
                $vitalData['patient_id'] = $this->admission->patient_id;
                $vitalData['admission_id'] = $this->admission->id;
                $vitalData['recorded_by'] = \Illuminate\Support\Facades\Auth::id();
                $vitalData['recorded_at'] = now();
                \App\Models\IpdVital::create($vitalData);
            }
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Admission updated successfully!']);
        $this->dispatch('close-modal', name: 'edit-admission-modal');
        $this->dispatch('refresh-admissions');
    }

    public function render()
    {
        return view('livewire.counter.ipd-admission-edit', [
            'doctors'       => Doctor::where('is_active', true)->with('user')->get(),
            'wards'         => Ward::all(),
            'reasons'       => ClinicalTemplate::where('type', 'reason')->get(),
            'clinicalNotes' => ClinicalTemplate::where('type', 'notes')->get(),
        ]);
    }
}
