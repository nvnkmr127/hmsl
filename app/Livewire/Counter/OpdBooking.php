<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\OpdManager;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

use Livewire\Attributes\On;

class OpdBooking extends Component
{
    use WithPagination;

    #[On('patient-registered')]
    public function handlePatientRegistered($id)
    {
        $this->selectPatient($id);
    }

    public $searchPatient = '';
    public $selectedPatient;
    public $selectedDoctor;
    public $consultation_date;
    public $valid_upto;
    public $weight;
    public $temperature;
    public $fee;
    public $paymentMode = 'Cash';
    public $notes;
    
    public $isEditing = false;
    public $editingId;
    public $lastConsultationId;

    public $showBookingForm = false;

    public function mount($patient_id = null)
    {
        $validityDays = \App\Models\Setting::get('opd_validity_days', 7);
        $this->consultation_date = date('Y-m-d');
        $this->valid_upto = date('Y-m-d', strtotime("+{$validityDays} days"));

        
        if ($patient_id) {
            $this->selectPatient($patient_id);
        }

        $this->autoSelectDoctor();
    }

    private function autoSelectDoctor()
    {
        $user = auth()->user();
        if ($user->hasRole('doctor_owner') || $user->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $this->selectedDoctor = $doctor->id;
                $this->fee = $doctor->consultation_fee ?: \App\Models\Setting::get('consultation_fee_default', 500);

            }
        } else {
            $doctor = Doctor::where('is_active', true)->first();
            if ($doctor) {
                $this->selectedDoctor = $doctor->id;
                $this->fee = $doctor->consultation_fee ?: \App\Models\Setting::get('consultation_fee_default', 500);

            }
        }
    }

    public function selectPatient($id)
    {
        $this->dispatch('notify', type: 'info', message: 'Initiating booking...');
        $this->selectedPatient = Patient::findOrFail($id);
        $this->showBookingForm = true;
        $this->isEditing = false;
        $this->searchPatient = ''; // Clear search
        
        // Auto-fill from latest vitals if available
        $latestVitals = $this->selectedPatient->vitals()->latest()->first();
        if ($latestVitals) {
            $this->weight = $latestVitals->weight;
            $this->temperature = $latestVitals->temperature;
        } else {
            $this->reset(['weight', 'temperature']);
        }

        $this->dispatch('open-modal', name: 'booking-modal');
    }

    public function updatedSelectedDoctor($id)
    {
        if ($id && !$this->isEditing) {
            $doctor = Doctor::find($id);
            if ($doctor) {
                $this->fee = $doctor->consultation_fee;
            }
        }
    }

    public function editBooking($id)
    {
        $consultation = Consultation::with('patient')->findOrFail($id);
        $this->editingId = $id;
        $this->isEditing = true;
        $this->selectedPatient = $consultation->patient;
        $this->selectedDoctor = $consultation->doctor_id;
        $this->weight = $consultation->weight;
        $this->temperature = $consultation->temperature;
        $this->consultation_date = \Carbon\Carbon::parse($consultation->consultation_date)->format('Y-m-d');
        $this->valid_upto = $consultation->valid_upto ? \Carbon\Carbon::parse($consultation->valid_upto)->format('Y-m-d') : null;

        $this->fee = $consultation->fee;
        $this->paymentMode = $consultation->payment_method;
        $this->notes = $consultation->notes;
        $this->showBookingForm = true;

        $this->dispatch('open-modal', name: 'booking-modal');
    }

    public function cancelBooking($id)
    {
        $this->authorize('view opd');
        $consultation = Consultation::findOrFail($id);
        $consultation->update(['status' => 'Cancelled']);
        
        $this->dispatch('notify', 
            type: 'warning',
            message: "Token #{$consultation->token_number} has been cancelled."
        );
    }

    public function restoreBooking($id)
    {
        $this->authorize('view opd');
        $consultation = Consultation::findOrFail($id);
        $consultation->update(['status' => 'Pending']);
        
        $this->dispatch('notify', 
            type: 'success',
            message: "Token #{$consultation->token_number} has been restored."
        );
    }

    public function book($shouldPrint = true)
    {
        $manager = app(OpdManager::class);
        $this->validate([
            'selectedPatient' => 'required',
            'selectedDoctor' => 'required|exists:doctors,id',
            'consultation_date' => 'required|date',
            'fee' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:500',
            'temperature' => 'nullable|numeric|min:70|max:120',
            'paymentMode' => 'required|in:Cash,UPI,Card',
        ]);


        if ($this->isEditing) {
            $consultation = Consultation::findOrFail($this->editingId);
            $consultation->update([
                'doctor_id' => $this->selectedDoctor,
                'weight' => $this->weight,
                'temperature' => $this->temperature,
                'fee' => $this->fee,
                'notes' => $this->notes,
            ]);
            $message = "Appointment updated successfully!";
        } else {
            // Duplicate booking check (Patient booked for same doctor on same day)
            $exists = Consultation::where('patient_id', $this->selectedPatient->id)
                ->where('doctor_id', $this->selectedDoctor)
                ->whereDate('consultation_date', $this->consultation_date ?: date('Y-m-d'))
                ->where('status', '!=', 'Cancelled')
                ->exists();

            if ($exists) {
                $this->dispatch('notify', type: 'error', message: 'Patient already has an active booking for this doctor on this date.');
                return;
            }

            $consultation = $manager->bookAppointment([
                'patient_id' => $this->selectedPatient->id,
                'doctor_id' => $this->selectedDoctor,
                'weight' => $this->weight,
                'temperature' => $this->temperature,
                'fee' => $this->fee,
                'consultation_date' => $this->consultation_date ?: date('Y-m-d'),
                'valid_upto' => $this->valid_upto ?: date('Y-m-d', strtotime('+' . \App\Models\Setting::get('opd_validity_days', 7) . ' days')),
                'payment_status' => 'Paid',

                'payment_method' => $this->paymentMode,
                'notes' => $this->notes,
            ]);
            $message = "Token #{$consultation->token_number} generated for {$this->selectedPatient->full_name}!";
            $this->lastConsultationId = $consultation->id;
            
            if ($shouldPrint) {
                $this->dispatch('print-op-slip', ['id' => $consultation->id]);
            }
        }

        $this->dispatch('notify', type: 'success', message: $message);

        $this->reset(['selectedPatient', 'fee', 'notes', 'showBookingForm', 'isEditing', 'editingId', 'weight', 'temperature', 'paymentMode', 'searchPatient', 'lastConsultationId']);
        $validityDays = \App\Models\Setting::get('opd_validity_days', 7);
        $this->consultation_date = date('Y-m-d');
        $this->valid_upto = date('Y-m-d', strtotime("+{$validityDays} days"));

        
        $this->autoSelectDoctor();

        $this->dispatch('close-modal', name: 'booking-modal');
        $this->dispatch('booking-completed');
    }

    public function clearLastBooking()
    {
        $this->lastConsultationId = null;
    }

    public function openPatientForm($phone = null)
    {
        $this->dispatch('notify', type: 'info', message: 'Opening registration form...');
        
        // Only pass search string as phone if it looks like a phone (all numeric)
        $validPhone = is_numeric($phone) ? $phone : null;
        $this->dispatch('create-patient', phone: $validPhone);
    }

    public function updatedSearchPatient($value)
    {
        // Auto search/select after typing 10 digits (mobile)
        if (strlen($value) === 10 && is_numeric($value)) {
            $patient = Patient::where('phone', $value)->first();
            if ($patient) {
                $this->selectPatient($patient->id);
            }
        }
    }

    public function render()
    {
        $patients = [];
        if (strlen($this->searchPatient) >= 3) {
            $patients = Patient::query()
                ->with(['consultations' => function($q) {
                    $q->latest()->limit(1);
                }])
                ->where(function($q) {
                    $q->where('phone', 'like', "%{$this->searchPatient}%")
                      ->orWhere('uhid', 'like', "%{$this->searchPatient}%")
                      ->orWhere('first_name', 'like', "%{$this->searchPatient}%");
                })
                ->limit(5)
                ->get();
        }

        $doctors = Doctor::query()->with(['department', 'user'])->where('is_active', true)->get();
        
        $todayConsultationsQuery = Consultation::with(['patient', 'doctor.user', 'patient.vitals' => function($query) {
                $query->latest()->limit(1);
            }])
            ->whereDate('consultation_date', date('Y-m-d'))
            ->when($this->selectedDoctor && !$this->showBookingForm, fn($query) => $query->where('doctor_id', $this->selectedDoctor));

        $stats = [
            'total' => (clone $todayConsultationsQuery)->count(),
            'pending' => (clone $todayConsultationsQuery)->where('status', 'Pending')->count(),
            'completed' => (clone $todayConsultationsQuery)->where('status', 'Completed')->count(),
            'cancelled' => (clone $todayConsultationsQuery)->where('status', 'Cancelled')->count(),
        ];

        $todayConsultations = $todayConsultationsQuery->latest()->paginate(10);

        return view('livewire.counter.opd-booking', [
            'patients' => $patients,
            'doctors' => $doctors,
            'todayConsultations' => $todayConsultations,
            'stats' => $stats
        ]);
    }
}
