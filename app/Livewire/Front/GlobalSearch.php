<?php

namespace App\Livewire\Front;

use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Doctor;
use Livewire\Component;

class GlobalSearch extends Component
{
    public $query = '';
    public $results = [];
    public $isOpen = false;

    public function updatedQuery($value)
    {
        if (strlen($value) < 2) {
            $this->results = [];
            $this->isOpen = false;
            return;
        }

        $this->isOpen = true;
        
        $patients = Patient::search($value)->limit(5)->get();
        $doctors = Doctor::search($value)->limit(5)->get();
        
        $consultations = Consultation::with('patient')
            ->where('token_number', 'like', "%{$value}%")
            ->orWhereHas('patient', function($q) use ($value) {
                $q->where('phone', 'like', "%{$value}%");
            })
            ->latest()
            ->limit(5)->get();

        $this->results = [
            'patients' => $patients,
            'doctors' => $doctors,
            'consultations' => $consultations,
        ];
    }

    public function handleEnter()
    {
        $query = trim($this->query);
        if (empty($query) || !\App\Models\Setting::get('enable_barcodes', false)) return;

        // Try to find a patient by exact UHID first (Barcode scanning case)
        $patient = Patient::where('uhid', $query)->first();
        
        if ($patient) {
            return redirect()->route('counter.patients.history', $patient->id);
        }

        // If not a direct UHID match, but we have exactly one result in patients, go there
        if (count($this->results['patients'] ?? []) === 1) {
            return redirect()->route('counter.patients.history', $this->results['patients'][0]->id);
        }
    }

    public function render()
    {
        return view('livewire.front.global-search');
    }
}
