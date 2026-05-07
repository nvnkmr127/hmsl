<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\OpdService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

use App\Services\GrowthChartService;
use Carbon\Carbon;

class QuickOpBooking extends Component
{
    public $searchPatient = '';
    public $searchType = 'all';
    public $selectedPatient;
    public $selectedDoctor;
    public $consultation_date;
    public $valid_upto;
    public $weight;
    public $height;
    public $temperature;
    public $fee;
    public $paymentMode = 'Cash';
    public $selectedService;
    public $notes;
    public $isEditing = false;
    public $editingId;
    public $isFollowUp = false;
    public $latestConsultation;
    public $isReview = false;
    public $growthStatus;
    public $growthForecast;
    public $activeBookingFound = false;

    #[On('patient-registered')]
    public function handlePatientRegistered($id = null)
    {
        // Handle both named array ['id' => 1] and direct argument 1
        $patientId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($patientId) {
            $this->selectPatient($patientId);
        }
    }

    #[On('patient-saved')]
    public function handlePatientSaved()
    {
        if ($this->selectedPatient) {
            $this->selectedPatient->refresh();
            $this->dispatch('open-modal', name: 'quick-op-modal');
        }
    }

    public function mount(OpdService $service)
    {
        $this->consultation_date = now()->toDateString();
        $this->valid_upto = $service->getValidityDate($this->consultation_date);
        $this->autoSelectDoctor();

        // Check for patient_id in request to auto-open
        $patientId = request()->get('patient_id');
        if ($patientId && is_numeric($patientId)) {
            $this->selectPatient($patientId);
        }
    }

    public function updatedSearchPatient($value)
    {
        // Auto search/select or trigger registration after 10 digits for phone search
        if (strlen($value) === 10 && is_numeric($value) && ($this->searchType === 'all' || $this->searchType === 'phone')) {
            $patients = Patient::where('phone', $value)->get();
            if ($patients->count() === 1) {
                $this->selectPatient($patients->first()->id);
            } elseif ($patients->count() > 1) {
                // Multiple siblings found - highlight search results and notify
                $this->searchPatient = $value; 
                $this->dispatch('notify', ['type' => 'info', 'message' => "Multiple patients found for this number. Please select one."]);
            } else {
                // Not found - auto close this and open registration
                $this->dispatch('close-modal', name: 'quick-op-modal');
                // Small delay to ensure smooth transition
                $this->dispatch('create-patient', ['phone' => $value]);
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
        $this->reset(['searchPatient', 'selectedPatient', 'selectedService', 'weight', 'height', 'temperature', 'notes', 'isEditing', 'isReview', 'isFollowUp']);
        $this->dispatch('open-modal', name: 'quick-op-modal');
    }

    public function selectPatient($id)
    {
        $this->selectedPatient = Patient::findOrFail($id);
        $this->isEditing = false;
        $this->searchPatient = '';
        
        $this->recalculateDetails();

        $latestVitals = $this->selectedPatient->vitals()->latest()->first();
        if ($latestVitals) {
            $this->weight = $latestVitals->weight;
            $this->height = $latestVitals->height;
            $this->temperature = $latestVitals->temperature;
        }

        $this->dispatch('open-modal', name: 'quick-op-modal');

        if ($this->weight || $this->height) {
            $this->updateGrowthStatus();
        }
    }

    public function recalculateDetails()
    {
        if (!$this->selectedPatient) return;

        $opdService = app(OpdService::class);
        $details = $opdService->calculateBookingDetails(
            $this->selectedPatient,
            $this->selectedService ?: null,
            $this->selectedDoctor ?: null,
            $this->consultation_date
        );

        $this->isReview = $details['is_review'];
        $this->isFollowUp = $details['is_follow_up'];
        $this->fee = $details['suggested_fee'];
        $this->valid_upto = $details['valid_upto'];
        $this->latestConsultation = $details['latest_consultation'];

        if ($this->isReview) {
            $this->selectedService = $this->latestConsultation->service_id;
            $this->selectedDoctor = $this->latestConsultation->doctor_id;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Review Visit: Auto-selected previous service.']);
        } elseif ($this->isFollowUp) {
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Follow-up visit: Free of charge.']);
        }

        $growthService = app(GrowthChartService::class);
        $this->growthForecast = $growthService->getGrowthForecast($this->selectedPatient);

        $this->checkActiveBooking();
    }

    public function recalculateFee()
    {
        $this->recalculateDetails();
    }

    private function checkActiveBooking()
    {
        if (!$this->selectedPatient || !$this->selectedService) {
            $this->activeBookingFound = false;
            return;
        }

        $this->activeBookingFound = Consultation::where('patient_id', $this->selectedPatient->id)
            ->where('service_id', $this->selectedService)
            ->whereDate('consultation_date', $this->consultation_date)
            ->whereIn('status', ['Pending', 'In Progress'])
            ->when($this->selectedDoctor, fn($q) => $q->where('doctor_id', $this->selectedDoctor))
            ->exists();
    }

    public function refreshValidity()
    {
        $opdService = app(OpdService::class);
        $this->valid_upto = $opdService->getValidityDate($this->consultation_date, $this->selectedService ?: null);
    }

    public function updatedSelectedService($id)
    {
        $this->recalculateFee();
        $this->checkActiveBooking();
    }

    public function updatedSelectedDoctor($id)
    {
        $this->checkActiveBooking();
    }

    public function updatedConsultationDate($value)
    {
        $this->checkActiveBooking();
        $this->refreshValidity();
    }

    public function updatedWeight($value)
    {
        $this->updateGrowthStatus();
    }

    public function updatedHeight($value)
    {
        $this->updateGrowthStatus();
    }

    protected function updateGrowthStatus()
    {
        if ($this->selectedPatient && ($this->weight || $this->height)) {
            $service = app(GrowthChartService::class);
            $this->growthStatus = $service->getGrowthStatus($this->selectedPatient, $this->weight, $this->height);
            $this->dispatch('growth-status-updated', $this->growthStatus);
        } else {
            $this->growthStatus = null;
        }
    }

    #[Computed]
    public function assignedDoctorName()
    {
        if (!$this->selectedDoctor) return 'Not Assigned';
        $doctor = Doctor::find($this->selectedDoctor);
        return $doctor ? ($doctor->full_name ?? 'Not Assigned') : 'Not Assigned';
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
                'visit_type' => $this->isReview ? 'Review' : ($this->isFollowUp ? 'Follow-up' : 'New'),
                'doctor_id' => $this->selectedDoctor,
                'weight' => $this->weight,
                'height' => $this->height,
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
            $patients = Patient::query();
            
            if ($this->searchType === 'uhid') {
                $patients->where('uhid', 'like', "%{$this->searchPatient}%");
            } elseif ($this->searchType === 'phone') {
                $patients->where('phone', 'like', "%{$this->searchPatient}%");
            } elseif ($this->searchType === 'mother_name') {
                $patients->where('mother_name', 'like', "%{$this->searchPatient}%");
            } elseif ($this->searchType === 'name') {
                $patients->where(function($q) {
                    $q->where('first_name', 'like', "%{$this->searchPatient}%")
                      ->orWhere('last_name', 'like', "%{$this->searchPatient}%");
                });
            } else {
                $patients->search($this->searchPatient);
            }

            $patients = $patients->limit(5)->get();
        }

        $services = \App\Models\Service::where('is_active', true)->where('category', 'OPD')->orderBy('sort_order')->get();

        return view('livewire.counter.quick-op-booking', [
            'patients' => $patients,
            'services' => $services,
        ]);
    }
}
