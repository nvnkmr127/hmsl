<?php

namespace App\Livewire\Ipd;

use App\Models\Admission;
use App\Models\DischargeSummary;
use App\Models\DischargeMedication;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManageDischargeMedications extends Component
{
    public Admission $admission;
    public $meds = [];

    public $frequencies = [
        'OD' => 'రోజుకు ఒకసారి (OD - Once Daily)',
        'BD' => 'రోజుకు రెండుసార్లు (BD - Twice Daily)',
        'TDS' => 'రోజుకు మూడుసార్లు (TDS - Thrice Daily)',
        'QID' => 'రోజుకు నాలుగుసార్లు (QID - Four Times Daily)',
        'SOS' => 'అవసరమైనప్పుడు (SOS - When Needed)'
    ];

    public $instructionsList = [
        'After food' => 'తిన్న తర్వాత (After Food)',
        'Before food' => 'తినక ముందు (Before Food)',
        'Empty stomach' => 'పరగడుపున (Empty Stomach)',
        'Apply locally' => 'పైపూతగా వాడాలి (Apply Locally)',
        'At bed time' => 'పడుకునే ముందు (At Bed Time)'
    ];

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->loadMedications();
    }

    public function loadMedications()
    {
        $summary = $this->admission->dischargeSummary;
        if ($summary && $summary->medications->count()) {
            $this->meds = $summary->medications->map(function ($med) {
                return [
                    'id' => $med->id,
                    'medicine_name' => $med->medicine_name,
                    'dosage' => $med->dosage,
                    'frequency' => $med->frequency,
                    'duration' => $med->duration,
                    'route' => $med->route,
                    'instructions' => $med->instructions,
                ];
            })->toArray();
        }

        // Ensure at least one empty row
        if (empty($this->meds)) {
            $this->addMedication();
        }
    }

    public function addMedication()
    {
        $this->meds[] = [
            'id' => null,
            'medicine_name' => '',
            'dosage' => '',
            'frequency' => '',
            'duration' => '',
            'route' => 'Oral', // Default type of intake
            'instructions' => '',
        ];
    }

    public function removeMedication($index)
    {
        unset($this->meds[$index]);
        $this->meds = array_values($this->meds);
    }

    public function save()
    {
        // Validation
        $this->validate([
            'meds.*.medicine_name' => 'required|string',
            'meds.*.dosage' => 'nullable|string',
            'meds.*.frequency' => 'required|string',
            'meds.*.duration' => 'nullable|string',
            'meds.*.route' => 'nullable|string',
            'meds.*.instructions' => 'nullable|string',
        ]);

        $summary = $this->admission->dischargeSummary;

        if (!$summary) {
            $summary = DischargeSummary::create([
                'admission_id' => $this->admission->id,
                'patient_id' => $this->admission->patient_id,
                'doctor_id' => $this->admission->doctor_id,
                'created_by' => Auth::id(),
                'admission_number' => $this->admission->admission_number,
                'uhid' => $this->admission->patient->uhid,
                'admission_date' => $this->admission->admission_date,
                'status' => 'Draft',
                'is_finalized' => false,
            ]);
        }

        // Delete old meds
        $summary->medications()->delete();

        // Add new meds
        foreach ($this->meds as $medData) {
            if (!empty($medData['medicine_name'])) {
                DischargeMedication::create([
                    'discharge_summary_id' => $summary->id,
                    'medicine_name' => $medData['medicine_name'],
                    'dosage' => $medData['dosage'],
                    'frequency' => $medData['frequency'],
                    'duration' => $medData['duration'],
                    'route' => $medData['route'],
                    'instructions' => $medData['instructions'],
                ]);
            }
        }

        $this->dispatch('close-modal', name: 'manage-discharge-meds-modal');
        return redirect()->route('discharge.summary', ['admission' => $this->admission->id]);
    }

    public function render()
    {
        return view('livewire.ipd.manage-discharge-medications');
    }
}
