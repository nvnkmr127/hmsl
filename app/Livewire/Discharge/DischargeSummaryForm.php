<?php

namespace App\Livewire\Discharge;

use App\Models\Admission;
use App\Models\ClinicalTemplate;
use App\Models\DischargeMedication;
use App\Models\DischargeSummary;
use App\Models\IpdMedicationChart;
use App\Services\DischargeSummaryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DischargeSummaryForm extends Component
{
    use WithPagination;

    public Admission $admission;
    public ?DischargeSummary $summary = null;

    public $activeTab = 'diagnosis';

    public $admission_diagnosis;
    public $final_diagnosis;
    public $treatment_summary;
    public $procedures_done;
    public $investigations_summary;

    public $condition_at_discharge;
    public $condition_notes;

    public $general_advice;
    public $diet_advice;
    public $activity_advice;

    public $follow_up_date;
    public $follow_up_notes;

    public $newMedName;
    public $newMedDosage;
    public $newMedFrequency;
    public $newMedDuration;
    public $newMedRoute = 'Oral';
    public $newMedInstructions;

    public $showMedForm = false;

    protected $rules = [
        'admission_diagnosis' => 'nullable|string',
        'final_diagnosis' => 'nullable|string',
        'treatment_summary' => 'nullable|string',
        'procedures_done' => 'nullable|string',
        'investigations_summary' => 'nullable|string',
        'condition_at_discharge' => 'nullable|in:Stable,Improved,Critical,Referred,Expired,LAMA',
        'condition_notes' => 'nullable|string',
        'general_advice' => 'nullable|string',
        'diet_advice' => 'nullable|string',
        'activity_advice' => 'nullable|string',
        'follow_up_date' => 'nullable|date',
        'follow_up_notes' => 'nullable|string',
    ];

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->loadSummary();
    }

    public function loadSummary()
    {
        $this->summary = DischargeSummary::where('admission_id', $this->admission->id)
            ->with('medications')
            ->first();

        if ($this->summary) {
            $this->admission_diagnosis = $this->summary->admission_diagnosis;
            $this->final_diagnosis = $this->summary->final_diagnosis;
            $this->treatment_summary = $this->summary->treatment_summary;
            $this->procedures_done = $this->summary->procedures_done;
            $this->investigations_summary = $this->summary->investigations_summary;
            $this->condition_at_discharge = $this->summary->condition_at_discharge;
            $this->condition_notes = $this->summary->condition_notes;
            $this->general_advice = $this->summary->general_advice;
            $this->diet_advice = $this->summary->diet_advice;
            $this->activity_advice = $this->summary->activity_advice;
            $this->follow_up_date = $this->summary->follow_up_date?->format('Y-m-d');
            $this->follow_up_notes = $this->summary->follow_up_notes;
        } else {
            $this->admission_diagnosis = $this->admission->reason_for_admission;
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveSection($section)
    {
        if (!$this->summary) {
            $service = app(DischargeSummaryService::class);
            $this->summary = $service->createDraft($this->admission);
        }

        $data = match ($section) {
            'diagnosis' => [
                'admission_diagnosis' => $this->admission_diagnosis,
                'final_diagnosis' => $this->final_diagnosis,
            ],
            'treatment' => [
                'treatment_summary' => $this->treatment_summary,
                'procedures_done' => $this->procedures_done,
                'investigations_summary' => $this->investigations_summary,
            ],
            'condition' => [
                'condition_at_discharge' => $this->condition_at_discharge,
                'condition_notes' => $this->condition_notes,
            ],
            'advice' => [
                'general_advice' => $this->general_advice,
                'diet_advice' => $this->diet_advice,
                'activity_advice' => $this->activity_advice,
            ],
            'followup' => [
                'follow_up_date' => $this->follow_up_date,
                'follow_up_notes' => $this->follow_up_notes,
            ],
            default => [],
        };

        $service = app(DischargeSummaryService::class);
        $service->update($this->summary, $data);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Section saved successfully'
        ]);
    }

    public function addMedication()
    {
        $this->validate([
            'newMedName' => 'required|string',
            'newMedDosage' => 'nullable|string',
            'newMedFrequency' => 'nullable|string',
            'newMedDuration' => 'nullable|string',
            'newMedRoute' => 'nullable|string',
            'newMedInstructions' => 'nullable|string',
        ]);

        if (!$this->summary) {
            $service = app(DischargeSummaryService::class);
            $this->summary = $service->createDraft($this->admission);
        }

        $service = app(DischargeSummaryService::class);
        $service->addMedication($this->summary, [
            'medicine_name' => $this->newMedName,
            'dosage' => $this->newMedDosage,
            'frequency' => $this->newMedFrequency,
            'duration' => $this->newMedDuration,
            'route' => $this->newMedRoute,
            'instructions' => $this->newMedInstructions,
            'is_continued' => true,
        ]);

        $this->reset(['newMedName', 'newMedDosage', 'newMedFrequency', 'newMedDuration', 'newMedRoute', 'newMedInstructions']);
        $this->showMedForm = false;
        $this->summary->refresh();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Medication added'
        ]);
    }

    public function removeMedication($medicationId)
    {
        $medication = DischargeMedication::findOrFail($medicationId);
        $service = app(DischargeSummaryService::class);
        $service->removeMedication($medication);
        $this->summary->refresh();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Medication removed'
        ]);
    }

    public function submitForReview()
    {
        if (!$this->summary) {
            $service = app(DischargeSummaryService::class);
            $this->summary = $service->createDraft($this->admission);
        }

        $service = app(DischargeSummaryService::class);
        $service->submitForReview($this->summary);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Submitted for review'
        ]);
    }

    public function returnToDraft()
    {
        $service = app(DischargeSummaryService::class);
        $service->returnToDraft($this->summary);

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Returned to draft'
        ]);
    }

    public function finalize()
    {
        try {
            $service = app(DischargeSummaryService::class);
            $service->finalize($this->summary, Auth::user());

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Discharge summary finalized'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function dischargePatient()
    {
        if (!$this->summary) {
            $service = app(DischargeSummaryService::class);
            $this->summary = $service->createDraft($this->admission);
        }

        $service = app(DischargeSummaryService::class);

        try {
            $admission = $service->dischargePatient($this->summary, Auth::user());

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Patient discharged successfully!'
            ]);

            return redirect()->route('counter.ipd.show', $admission->id);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function importFromMedicationChart()
    {
        if (!$this->summary) {
            $service = app(DischargeSummaryService::class);
            $this->summary = $service->createDraft($this->admission);
        }

        $activeMeds = IpdMedicationChart::where('admission_id', $this->admission->id)
            ->where('status', 'Active')
            ->get();

        foreach ($activeMeds as $med) {
            $this->newMedName = $med->medicine_name;
            $this->newMedDosage = $med->dosage;
            $this->newMedFrequency = $med->frequency;
            $this->newMedDuration = $med->duration;
            $this->newMedRoute = $med->route;
            $this->newMedInstructions = $med->instructions;
            $this->addMedication();
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Medications imported from chart'
        ]);
    }

    public function render()
    {
        $this->summary?->refresh();

        $templates = ClinicalTemplate::where('type', 'notes')->get();

        $conditionOptions = ['Stable', 'Improved', 'Critical', 'Referred', 'Expired', 'LAMA'];
        $frequencyOptions = ['OD', 'BD', 'TDS', 'QID', 'SOS', 'PRN', 'Stat'];
        $routeOptions = ['Oral', 'IV', 'IM', 'SC', 'Inhalation', 'Topical', 'Rectal', 'Sublingual'];

        return view('livewire.discharge.discharge-summary-form', [
            'templates' => $templates,
            'conditionOptions' => $conditionOptions,
            'frequencyOptions' => $frequencyOptions,
            'routeOptions' => $routeOptions,
        ]);
    }
}
