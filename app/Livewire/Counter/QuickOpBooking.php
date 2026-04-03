<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\OpdService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class QuickOpBooking extends Component
{
    public $searchPatient = '';
    public $selectedPatient;
    public $selectedDoctor;
    public $consultation_date;
    public $valid_upto;
    public $weight;
    public $temperature;
    public $fee;
    public $paymentMode = 'Cash';
    public $selectedService;
    public $notes;
    public $isEditing = false;
    public $editingId;

    #[On('patient-registered')]
    public function handlePatientRegistered($id = null)
    {
        // Handle both named array ['id' => 1] and direct argument 1
        $patientId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($patientId) {
            $this->selectPatient($patientId);
        }
    }

    public function mount(OpdService $service)
    {
        $this->consultation_date = now()->toDateString();
        $this->valid_upto = $service->getValidityDate($this->consultation_date);
        $this->autoSelectDoctor();
    }

    public function updatedSearchPatient($value)
    {
        // Auto search/select or trigger registration after 10 digits
        if (strlen($value) === 10 && is_numeric($value)) {
            $patient = Patient::where('phone', $value)->first();
            if ($patient) {
                $this->selectPatient($patient->id);
            } else {
                // Not found - auto close this and open registration
                $this->dispatch('close-modal', name: 'quick-op-modal');
                // Small delay to ensure smooth transition
                $this->dispatch('create-patient', phone: $value);
            }
        }
    }

    private function autoSelectDoctor()
    {
        $user = auth()->user();
        if ($user->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $this->selectedDoctor = $doctor->id;
                $this->fee = $doctor->consultation_fee;
            }
        } else {
            $doctor = Doctor::where('is_active', true)->first();
            if ($doctor) {
                $this->selectedDoctor = $doctor->id;
                $this->fee = $doctor->consultation_fee;
            }
        }
    }

    #[On('quick-op-booking')]
    public function open()
    {
        $this->reset(['searchPatient', 'selectedPatient', 'selectedService', 'weight', 'temperature', 'notes', 'isEditing']);
        $this->dispatch('open-modal', name: 'quick-op-modal');
    }

    public function selectPatient($id)
    {
        $this->selectedPatient = Patient::findOrFail($id);
        $this->isEditing = false;
        $this->searchPatient = '';
        
        $latestVitals = $this->selectedPatient->vitals()->latest()->first();
        if ($latestVitals) {
            $this->weight = $latestVitals->weight;
            $this->temperature = $latestVitals->temperature;
        }

        $this->dispatch('open-modal', name: 'quick-op-modal');
    }

    public function updatedSelectedService($id)
    {
        if ($id) {
            $service = \App\Models\Service::find($id);
            if ($service) {
                $this->fee = $service->price;
            }
        }
    }

    public function updatedSelectedDoctor($id)
    {
        if ($id && !$this->isEditing && !$this->selectedService) {
            $doctor = Doctor::find($id);
            if ($doctor) {
                $this->fee = $doctor->consultation_fee;
            }
        }
    }

    public function book($shouldPrint = true)
    {
        \Illuminate\Support\Facades\Log::debug('QUICK_OP_BOOKING_START', [
            'shouldPrint' => $shouldPrint,
            'patient_id' => $this->selectedPatient?->id,
            'service' => $this->selectedService,
            'fee' => $this->fee
        ]);

        $service = app(OpdService::class);
        $this->validate([
            'selectedPatient' => 'required',
            'selectedService' => 'required|exists:services,id',
            'fee' => 'required|numeric|min:0',
            'paymentMode' => 'required|in:Cash,UPI,Card',
        ]);

        try {
            $consultation = $service->bookAppointment([
                'patient_id' => $this->selectedPatient->id,
                'service_id' => $this->selectedService,
                'doctor_id' => $this->selectedDoctor,
                'weight' => $this->weight,
                'temperature' => $this->temperature,
                'fee' => $this->fee,
                'consultation_date' => $this->consultation_date,
                'valid_upto' => $this->valid_upto,
                'payment_status' => 'Paid',
                'payment_method' => $this->paymentMode,
                'notes' => $this->notes,
            ]);

            \Illuminate\Support\Facades\Log::info('QUICK_OP_BOOKING_SUCCESS', ['id' => $consultation->id]);

            $this->dispatch('notify', ['type' => 'success', 'message' => "Registration Success: #{$consultation->token_number}"]);
            
            if ($shouldPrint) {
                \Illuminate\Support\Facades\Log::debug('QUICK_OP_BOOKING_DISPATCH_PRINT', ['id' => $consultation->id]);
                $this->dispatch('print-op-slip', ['id' => $consultation->id]);
            }

            $this->dispatch('close-modal', name: 'quick-op-modal');
            $this->dispatch('booking-completed');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('QUICK_OP_BOOKING_FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $patients = [];
        if (strlen($this->searchPatient) >= 3) {
            $patients = Patient::search($this->searchPatient)->limit(5)->get();
        }

        $doctors = Doctor::active()->with(['user', 'department'])->get();
        $services = \App\Models\Service::where('is_active', true)->where('category', 'OPD')->get();

        return view('livewire.counter.quick-op-booking', [
            'patients' => $patients,
            'doctors' => $doctors,
            'services' => $services,
        ]);
    }
}
