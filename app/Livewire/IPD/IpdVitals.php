<?php

namespace App\Livewire\Ipd;

use App\Models\Admission;
use App\Models\IpdVital;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IpdVitals extends Component
{
    public Admission $admission;

    public $bp_systolic;
    public $bp_diastolic;
    public $pulse;
    public $temperature;
    public $spo2;
    public $resp_rate;
    public $weight;
    public $height;
    public $bmi;
    public $pain_scale;
    public $notes;

    public $showForm = false;

    protected $rules = [
        'bp_systolic' => 'nullable|numeric|min:50|max:300',
        'bp_diastolic' => 'nullable|numeric|min:30|max:200',
        'pulse' => 'nullable|numeric|min:30|max:250',
        'temperature' => 'nullable|numeric|min:90|max:110',
        'spo2' => 'nullable|numeric|min:50|max:100',
        'resp_rate' => 'nullable|numeric|min:5|max:60',
        'weight' => 'nullable|numeric|min:0.1|max:500',
        'height' => 'nullable|numeric|min:1|max:300',
        'pain_scale' => 'nullable|numeric|min:0|max:10',
        'notes' => 'nullable|string',
    ];

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
    }

    public function updatedWeight($value)
    {
        if ($value && $this->height) {
            $this->calculateBMI();
        }
    }

    public function updatedHeight($value)
    {
        if ($value && $this->weight) {
            $this->calculateBMI();
        }
    }

    public function calculateBMI()
    {
        if ($this->weight && $this->height) {
            $heightInMeters = $this->height / 100;
            $this->bmi = round($this->weight / ($heightInMeters * $heightInMeters), 1);
        }
    }

    public function save()
    {
        $this->validate();

        IpdVital::create([
            'admission_id' => $this->admission->id,
            'patient_id' => $this->admission->patient_id,
            'recorded_by' => Auth::id(),
            'recorded_at' => now(),
            'bp_systolic' => $this->bp_systolic,
            'bp_diastolic' => $this->bp_diastolic,
            'pulse' => $this->pulse,
            'temperature' => $this->temperature,
            'spo2' => $this->spo2,
            'resp_rate' => $this->resp_rate,
            'weight' => $this->weight,
            'height' => $this->height,
            'bmi' => $this->bmi,
            'pain_scale' => $this->pain_scale,
            'notes' => $this->notes,
        ]);

        $this->reset(['bp_systolic', 'bp_diastolic', 'pulse', 'temperature', 'spo2', 'resp_rate', 'weight', 'height', 'bmi', 'pain_scale', 'notes']);
        $this->showForm = false;

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Vitals recorded successfully']);
    }

    public function getVitalsHistoryProperty()
    {
        return IpdVital::where('admission_id', $this->admission->id)
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    public function getLatestVitalProperty()
    {
        return IpdVital::where('admission_id', $this->admission->id)
            ->orderBy('recorded_at', 'desc')
            ->first();
    }

    public function getTrendProperty()
    {
        return IpdVital::where('admission_id', $this->admission->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.ipd.ipd-vitals');
    }
}
