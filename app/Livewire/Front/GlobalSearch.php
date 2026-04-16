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
        $doctors = Doctor::where('first_name', 'like', "%{$value}%")
            ->orWhere('last_name', 'like', "%{$value}%")
            ->limit(5)->get();
        
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

    public function render()
    {
        return view('livewire.front.global-search');
    }
}
