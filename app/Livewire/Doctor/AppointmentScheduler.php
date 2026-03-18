<?php

namespace App\Livewire\Doctor;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\OpdManager;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class AppointmentScheduler extends Component
{
    public $searchPatient = '';
    public $selectedPatient;
    public $consultation_date;
    public $notes;
    
    #[On('open-scheduler')]
    public function open()
    {
        $this->reset(['searchPatient', 'selectedPatient', 'notes']);
        $this->consultation_date = date('Y-m-d');
        $this->dispatch('open-modal', ['name' => 'appointment-modal']);
    }

    public function selectPatient($id)
    {
        $this->selectedPatient = Patient::findOrFail($id);
        $this->searchPatient = '';
    }

    public function schedule(OpdManager $manager)
    {
        $this->validate([
            'selectedPatient' => 'required',
            'consultation_date' => 'required|date',
        ]);

        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (!$doctor) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Doctor profile not found.']);
            return;
        }

        $consultation = $manager->bookAppointment([
            'patient_id' => $this->selectedPatient->id,
            'doctor_id' => $doctor->id,
            'fee' => $doctor->consultation_fee,
            'consultation_date' => $this->consultation_date,
            'notes' => $this->notes,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Appointment scheduled! Token #{$consultation->token_number}"
        ]);

        $this->dispatch('close-modal', ['name' => 'appointment-modal']);
        $this->dispatch('appointment-scheduled');
    }

    public function render()
    {
        $patients = [];
        if (strlen($this->searchPatient) >= 3) {
            $patients = Patient::query()
                ->where(fn($q) => $q->where('first_name', 'like', "%{$this->searchPatient}%")
                    ->orWhere('last_name', 'like', "%{$this->searchPatient}%"))
                ->orWhere(function($q) {
                    $q->where('uhid', 'like', "%{$this->searchPatient}%");
                })
                ->limit(5)
                ->get();
        }

        return view('livewire.doctor.appointment-scheduler', compact('patients'));
    }
}
