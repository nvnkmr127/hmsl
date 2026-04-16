<?php

namespace App\Livewire\OPD;

use App\Models\Consultation;
use App\Models\Diagnosis;
use App\Models\Patient;
use App\Models\PatientVital;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CaseSheetEditor extends Component
{
    public Consultation $consultation;
    public Patient $patient;

    public $activeTab = 'vitals';

    public $bp_systolic;
    public $bp_diastolic;
    public $pulse;
    public $temperature;
    public $spo2;
    public $weight;
    public $height;
    public $resp_rate;

    public $chief_complaints = [];
    public $newComplaint = '';
    public $history_of_present_illness;
    public $past_history;
    public $personal_history;

    public $examination_findings;
    public $general_examination;
    public $systemic_examination;

    public $diagnosis_name;
    public $icd_code;
    public $diagnosis_type = 'Primary';
    public $diagnoses = [];

    public $newInstruction;

    protected $rules = [
        'bp_systolic' => 'nullable|numeric|min:50|max:300',
        'bp_diastolic' => 'nullable|numeric|min:30|max:200',
        'pulse' => 'nullable|numeric|min:30|max:250',
        'temperature' => 'nullable|numeric|min:90|max:110',
        'spo2' => 'nullable|numeric|min:50|max:100',
        'weight' => 'nullable|numeric|min:0.1|max:500',
        'height' => 'nullable|numeric|min:1|max:300',
        'resp_rate' => 'nullable|numeric|min:5|max:60',
        'history_of_present_illness' => 'nullable|string',
        'past_history' => 'nullable|string',
        'personal_history' => 'nullable|string',
        'general_examination' => 'nullable|string',
        'systemic_examination' => 'nullable|string',
        'examination_findings' => 'nullable|string',
    ];

    public function mount(Consultation $consultation)
    {
        $this->consultation = $consultation;
        $this->patient = $consultation->patient;

        $this->loadExistingData();
    }

    public function loadExistingData()
    {
        $vitals = PatientVital::where('patient_id', $this->patient->id)
            ->where('consultation_id', $this->consultation->id)
            ->latest()
            ->first();

        if ($vitals) {
            $this->bp_systolic = $vitals->bp_systolic;
            $this->bp_diastolic = $vitals->bp_diastolic;
            $this->pulse = $vitals->pulse;
            $this->temperature = $vitals->temperature;
            $this->spo2 = $vitals->spo2;
            $this->weight = $vitals->weight;
            $this->height = $vitals->height;
            $this->resp_rate = $vitals->resp_rate;
        }

        $this->chief_complaints = $this->consultation->chief_complaints ?? [];
        $this->history_of_present_illness = $this->consultation->history_of_present_illness;
        $this->past_history = $this->consultation->past_history;
        $this->personal_history = $this->consultation->personal_history;
        $this->general_examination = $this->consultation->general_examination;
        $this->systemic_examination = $this->consultation->systemic_examination;
        $this->examination_findings = $this->consultation->examination_findings;

        $this->diagnoses = Diagnosis::where('consultation_id', $this->consultation->id)
            ->get()
            ->toArray();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveVitals()
    {
        $this->validate([
            'bp_systolic' => 'nullable|numeric|min:50|max:300',
            'bp_diastolic' => 'nullable|numeric|min:30|max:200',
            'pulse' => 'nullable|numeric|min:30|max:250',
            'temperature' => 'nullable|numeric|min:90|max:110',
            'spo2' => 'nullable|numeric|min:50|max:100',
            'weight' => 'nullable|numeric|min:0.1|max:500',
            'height' => 'nullable|numeric|min:1|max:300',
            'resp_rate' => 'nullable|numeric|min:5|max:60',
        ]);

        PatientVital::updateOrCreate(
            [
                'patient_id' => $this->patient->id,
                'consultation_id' => $this->consultation->id,
            ],
            [
                'recorded_by' => Auth::id(),
                'bp_systolic' => $this->bp_systolic,
                'bp_diastolic' => $this->bp_diastolic,
                'pulse' => $this->pulse,
                'temperature' => $this->temperature,
                'spo2' => $this->spo2,
                'weight' => $this->weight,
                'height' => $this->height,
                'resp_rate' => $this->resp_rate,
            ]
        );

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Vitals saved successfully']);
    }

    public function addComplaint()
    {
        if (trim($this->newComplaint)) {
            $this->chief_complaints[] = trim($this->newComplaint);
            $this->newComplaint = '';
        }
    }

    public function removeComplaint($index)
    {
        unset($this->chief_complaints[$index]);
        $this->chief_complaints = array_values($this->chief_complaints);
    }

    public function saveComplaints()
    {
        $this->consultation->update([
            'chief_complaints' => $this->chief_complaints,
            'history_of_present_illness' => $this->history_of_present_illness,
            'past_history' => $this->past_history,
            'personal_history' => $this->personal_history,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'History saved successfully']);
    }

    public function saveExamination()
    {
        $this->validate([
            'general_examination' => 'nullable|string',
            'systemic_examination' => 'nullable|string',
            'examination_findings' => 'nullable|string',
        ]);

        $this->consultation->update([
            'general_examination' => $this->general_examination,
            'systemic_examination' => $this->systemic_examination,
            'examination_findings' => $this->examination_findings,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Examination saved successfully']);
    }

    public function addDiagnosis()
    {
        if (!trim($this->diagnosis_name)) {
            return;
        }

        Diagnosis::create([
            'patient_id' => $this->patient->id,
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->consultation->doctor_id,
            'created_by' => Auth::id(),
            'diagnosis_name' => trim($this->diagnosis_name),
            'icd_code' => $this->icd_code ?: null,
            'type' => count($this->diagnoses) === 0 ? 'Primary' : $this->diagnosis_type,
            'status' => 'Confirmed',
            'diagnosed_date' => now(),
        ]);

        $this->diagnosis_name = '';
        $this->icd_code = '';
        $this->diagnosis_type = 'Secondary';
        $this->diagnoses = Diagnosis::where('consultation_id', $this->consultation->id)->get()->toArray();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Diagnosis added']);
    }

    public function removeDiagnosis($id)
    {
        Diagnosis::where('id', $id)->delete();
        $this->diagnoses = Diagnosis::where('consultation_id', $this->consultation->id)->get()->toArray();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Diagnosis removed']);
    }

    public function render()
    {
        $this->diagnoses = Diagnosis::where('consultation_id', $this->consultation->id)->get()->toArray();

        return view('livewire.opd.case-sheet-editor');
    }
}
