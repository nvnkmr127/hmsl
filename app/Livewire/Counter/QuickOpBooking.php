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
    public $admissionDate;
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
    public $isEmergency = false;
    public $isNewbornBenefit = false;
    public $isIpd = false;
    public $wardId;
    public $bedId;
    public $reason;
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
        $this->isIpd = request()->routeIs('counter.ipd.*');
        $this->consultation_date = now()->toDateString();
        $this->admissionDate = now()->format('Y-m-d\TH:i');
        $this->valid_upto = $service->getValidityDate($this->consultation_date);
        $this->autoSelectDoctor();

        // Check for patient_id in request to auto-open
        $patientId = request()->get('patient_id');
        if ($patientId && is_numeric($patientId)) {
            $this->selectPatient($patientId);
        }
    }

    public function handleEnter()
    {
        $query = trim($this->searchPatient);
        if (empty($query) || !\App\Models\Setting::get('enable_barcodes', false)) return;

        // Try to find a patient by exact UHID first (Barcode scanning case)
        $patient = Patient::where('uhid', $query)->first();
        
        if ($patient) {
            $this->selectPatient($patient->id);
            return;
        }

        // If not a direct UHID match, but we have exactly one result in the current search, select it
        $results = Patient::search($this->searchPatient)->limit(2)->get();
        if ($results->count() === 1) {
            $this->selectPatient($results->first()->id);
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
    public function open($edit_id = null, $patient_id = null)
    {
        if ($edit_id) {
            $this->editBooking($edit_id);
        } else {
            $this->reset(['searchPatient', 'selectedPatient', 'selectedService', 'weight', 'height', 'temperature', 'notes', 'isEditing', 'editingId', 'isReview', 'isFollowUp']);
            
            if ($patient_id) {
                $this->selectPatient($patient_id);
            } else {
                $this->dispatch('open-modal', name: 'quick-op-modal');
            }
        }
    }

    public function editBooking($id)
    {
        $consultation = Consultation::with(['patient', 'bill.payments'])->findOrFail($id);
        $this->editingId = $id;
        $this->isEditing = true;
        $this->selectedPatient = $consultation->patient;
        $this->selectedService = $consultation->service_id;
        $this->selectedDoctor = $consultation->doctor_id;

        $this->weight = $consultation->weight ? (float)$consultation->weight : null;
        $this->height = $consultation->height ? (float)$consultation->height : null;
        $this->temperature = $consultation->temperature ? (float)$consultation->temperature : null;
        
        $this->consultation_date = \Carbon\Carbon::parse($consultation->consultation_date)->format('Y-m-d');
        $this->valid_upto = $consultation->valid_upto ? \Carbon\Carbon::parse($consultation->valid_upto)->format('Y-m-d') : null;

        $this->fee = $consultation->fee;
        $this->paymentMode = $consultation->payment_method ?: 'Cash';
        $this->notes = $consultation->notes;
        
        $this->dispatch('open-modal', name: 'quick-op-modal');

        // Refresh clinical logic (Review/Follow-up badges)
        $this->recalculateDetails();
        
        // Re-set values that recalculateDetails might have overwritten with defaults
        $this->fee = $consultation->fee;
        $this->valid_upto = $consultation->valid_upto ? \Carbon\Carbon::parse($consultation->valid_upto)->format('Y-m-d') : null;

        if ($this->weight || $this->height) {
            $this->updateGrowthStatus();
        }
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
        $this->isEmergency = $details['is_emergency'] ?? false;
        $this->isNewbornBenefit = $details['is_newborn_benefit'] ?? false;

        if ($this->isReview) {
            $this->selectedService = $this->latestConsultation->service_id;
            $this->selectedDoctor = $this->latestConsultation->doctor_id;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Review Visit: Auto-selected previous service.']);
        } elseif ($details['is_newborn_benefit'] ?? false) {
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Newborn Benefit: Free consultation applied (Delivery Attended).']);
        } elseif ($details['is_emergency'] ?? false) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Emergency Hours: Flat ₹500 fee applied.']);
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

    public function updatedWardId()
    {
        $this->bedId = null;
    }

    public function getAvailableBedsProperty()
    {
        if (!$this->wardId) return collect();
        return \App\Models\Bed::where('ward_id', $this->wardId)
            ->where('is_available', true)
            ->get();
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
        if ($this->isIpd) {
            return $this->bookAdmission();
        }

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
        ], [
            'selectedPatient.required' => 'Please select a patient first.',
            'selectedService.required' => 'Please select a consultation service.',
        ]);

        try {
            if ($this->isEditing) {
                $consultation = Consultation::findOrFail($this->editingId);
                
                $service->updateAppointment($consultation, [
                    'service_id' => $this->selectedService,
                    'doctor_id' => $this->selectedDoctor,
                    'weight' => $this->weight,
                    'height' => $this->height,
                    'temperature' => $this->temperature,
                    'fee' => $this->fee,
                    'consultation_date' => $this->consultation_date,
                    'valid_upto' => $this->valid_upto,
                    'payment_method' => $this->paymentMode,
                    'notes' => $this->notes,
                ]);

                $this->dispatch('notify', ['type' => 'success', 'message' => "Visit updated successfully."]);

            } else {
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
                    'bill_payment_status' => 'Paid',
                    'paid_amount' => $this->fee,
                    'notes' => $this->notes,
                ]);
                $this->dispatch('notify', ['type' => 'success', 'message' => "Registration Success: #{$consultation->token_number}"]);
            }
            
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

    public function bookAdmission()
    {
        $this->validate([
            'selectedPatient' => 'required',
            'selectedDoctor' => 'required|exists:doctors,id',
            'wardId' => 'required|exists:wards,id',
            'bedId' => 'required|exists:beds,id',
            'admissionDate' => 'required',
        ]);

        $service = app(\App\Services\IpdService::class);
        $data = [
            'patient_id' => $this->selectedPatient->id,
            'doctor_id' => $this->selectedDoctor,
            'ward_id' => $this->wardId,
            'bed_id' => $this->bedId,
            'admission_date' => $this->admissionDate,
            'reason_for_admission' => $this->reason,
            'weight' => $this->weight,
            'height' => $this->height,
            'temperature' => $this->temperature,
            'pulse' => $this->pulse,
            'bp_systolic' => $this->bp_systolic,
            'bp_diastolic' => $this->bp_diastolic,
            'respiratory_rate' => $this->respiratory_rate,
            'spo2' => $this->spo2,
        ];

        try {
            $admission = $service->admitPatient($data);
            $this->dispatch('notify', ['type' => 'success', 'message' => "Patient admitted successfully: {$admission->admission_number}"]);
            $this->dispatch('close-modal', name: 'quick-op-modal');
            return redirect()->route('counter.ipd.show', $admission->id);
        } catch (\Exception $e) {
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
            'doctors' => \App\Models\Doctor::where('is_active', true)->get(),
            'wards' => \App\Models\Ward::all(),
            'reasons' => \App\Models\ClinicalTemplate::where('type', 'reason')->get(),
        ]);
    }
}
