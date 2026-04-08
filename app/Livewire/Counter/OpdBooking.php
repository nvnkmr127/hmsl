<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\HospitalOwner;
use App\Services\OpdService;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class OpdBooking extends Component
{
    use WithPagination;

    #[On('patient-registered')]
    public function handlePatientRegistered($id = null)
    {
        // Handle both named array ['id' => 1] and direct argument 1
        $patientId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($patientId) {
            $this->selectPatient($patientId);
        }
    }


    public $searchPatient = '';
    public ?Patient $selectedPatient = null;
    public $selectedDoctor;
    public $consultation_date;
    public $valid_upto;
    public $weight;
    public $temperature;
    public $fee;
    public $paymentMode = 'Cash';
    public $paymentStatus = 'Paid';
    public $amountPaid = 0;
    public $selectedService;
    public $notes;
    public $isFollowUp = false;

    
    public $isEditing = false;
    public $editingId;
    public $lastConsultationId;

    public $showBookingForm = false;

    public function mount($patient_id = null, OpdService $service)
    {
        $this->consultation_date = now()->toDateString();
        $this->valid_upto = $service->getValidityDate($this->consultation_date);

        
        if ($patient_id) {
            $this->selectPatient($patient_id);
        }

        $this->autoSelectDoctor();
    }

    private function autoSelectDoctor()
    {
        $user = Auth::user();
        if (HospitalOwner::isOwner($user)) {
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
        $this->dispatch('notify', ['type' => 'info', 'message' => 'Initiating booking...']);

        $this->selectedPatient = Patient::findOrFail($id);
        $this->showBookingForm = true;
        $this->isEditing = false;
        $this->searchPatient = ''; // Clear search
        
        // Check for recent consultations within validity period
        $hasRecentVisit = Consultation::where('patient_id', $id)
            ->where('status', '!=', 'Cancelled')
            ->where('valid_upto', '>=', $this->consultation_date)
            ->exists();

        $this->isFollowUp = $hasRecentVisit;

        if ($hasRecentVisit) {
            $this->fee = 0;
            $this->paymentStatus = 'Paid';
            $this->amountPaid = 0;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Follow-up visit: Free of charge.']);
        } else {
            // Re-select doctor or service to get current fee
            if ($this->selectedService) {
                $service = \App\Models\Service::find($this->selectedService);
                if ($service) {
                    $this->fee = $service->price;
                }
            } else {
                $this->autoSelectDoctor();
            }
        }

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

    public function updatedSelectedService($id)
    {
        if ($id) {
            $service = \App\Models\Service::find($id);
            if ($service) {
                $this->fee = $service->price;
                
                // If patient selected, check for follow-up
                if ($this->selectedPatient) {
                    $hasRecentVisit = Consultation::where('patient_id', $this->selectedPatient->id)
                        ->where('status', '!=', 'Cancelled')
                        ->where('valid_upto', '>=', $this->consultation_date)
                        ->exists();
                    $this->isFollowUp = $hasRecentVisit;
                    if ($hasRecentVisit) {
                        $this->fee = 0;
                    }
                }
            }
        }
    }

    public function updatedSelectedDoctor($id)
    {
        if ($id && !$this->isEditing && !$this->selectedService) {
            $doctor = Doctor::find($id);
            if ($doctor) {
                $this->fee = $doctor->consultation_fee;
                $this->updatedFee($this->fee);

                // If patient selected, check for follow-up
                if ($this->selectedPatient) {
                    $hasRecentVisit = Consultation::where('patient_id', $this->selectedPatient->id)
                        ->where('status', '!=', 'Cancelled')
                        ->where('valid_upto', '>=', $this->consultation_date)
                        ->exists();
                    $this->isFollowUp = $hasRecentVisit;
                    if ($hasRecentVisit) {
                        $this->fee = 0;
                    }
                }
            }
        }
    }

    public function updatedFee($value)
    {
        $fee = (float) $value;
        if ($fee <= 0) {
            $this->paymentStatus = 'Paid';
            $this->amountPaid = 0;
            return;
        }

        if ($this->paymentStatus === 'Paid') {
            $this->amountPaid = $fee;
        } elseif ($this->paymentStatus === 'Unpaid') {
            $this->amountPaid = 0;
        } else {
            $this->amountPaid = min(max(0, (float) $this->amountPaid), $fee);
        }
    }

    public function updatedPaymentStatus($value)
    {
        $fee = (float) $this->fee;
        if ($fee <= 0) {
            $this->paymentStatus = 'Paid';
            $this->amountPaid = 0;
            return;
        }

        if ($value === 'Paid') {
            $this->amountPaid = $fee;
        } elseif ($value === 'Unpaid') {
            $this->amountPaid = 0;
        } else {
            $this->amountPaid = min(max(0, (float) $this->amountPaid), $fee);
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

        $this->weight = $consultation->weight;
        $this->temperature = $consultation->temperature;
        $this->consultation_date = \Carbon\Carbon::parse($consultation->consultation_date)->format('Y-m-d');
        $this->valid_upto = $consultation->valid_upto ? \Carbon\Carbon::parse($consultation->valid_upto)->format('Y-m-d') : null;

        $this->fee = $consultation->fee;
        $this->paymentMode = $consultation->payment_method;
        $this->paymentStatus = $consultation->bill?->payment_status ?: ($consultation->payment_status === 'Paid' ? 'Paid' : 'Unpaid');
        $this->amountPaid = $consultation->bill ? (float) $consultation->bill->paid_amount : ($consultation->payment_status === 'Paid' ? (float) $consultation->fee : 0);
        $this->notes = $consultation->notes;
        $this->showBookingForm = true;

        $this->dispatch('open-modal', name: 'booking-modal');
    }

    public function cancelBooking($id)
    {
        $this->authorize('view opd');
        $billing = app(BillingService::class);
        $tokenNumber = Consultation::whereKey($id)->value('token_number');

        DB::transaction(function () use ($id, $billing) {
            $consultation = Consultation::with(['bill.payments'])->lockForUpdate()->findOrFail($id);

            if ($consultation->status === 'Cancelled') {
                return;
            }

            $bill = $consultation->bill;
            if ($bill) {
                $bill->load('payments');
                $paid = (float) $bill->paid_amount;

                if ($paid > 0) {
                    $billing->recordPayment($bill, $paid, $bill->payment_method, 'refund', null, 'OPD token cancelled - refund');
                    $billing->recalculatePaymentStatus($bill);
                } else {
                    $bill->items()->delete();
                    $bill->delete();
                }
            }

            $consultation->update([
                'status' => 'Cancelled',
                'payment_status' => 'Unpaid',
                'payment_method' => null,
            ]);
        });
        
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => "Token #{$tokenNumber} has been cancelled."
        ]);

    }

    public function restoreBooking($id)
    {
        $this->authorize('view opd');
        $consultation = Consultation::findOrFail($id);
        $consultation->update(['status' => 'Pending']);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Token #{$consultation->token_number} has been restored."
        ]);

    }

    public function book($shouldPrint = true)
    {
        \Illuminate\Support\Facades\Log::debug('OPD_BOOKING_START', [
            'shouldPrint' => $shouldPrint,
            'isEditing' => $this->isEditing,
            'patient_id' => $this->selectedPatient?->id,
            'service' => $this->selectedService,
            'doctor' => $this->selectedDoctor,
            'fee' => $this->fee
        ]);

        $service = app(OpdService::class);
        $this->validate([
            'selectedPatient' => 'required',
            'selectedService' => 'required|exists:services,id',
            'selectedDoctor' => 'nullable|exists:doctors,id',
            'consultation_date' => 'required|date',
            'fee' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:500',
            'temperature' => 'nullable|numeric|min:70|max:120',
            'paymentMode' => 'required|in:Cash,UPI,Card',
            'paymentStatus' => 'required|in:Paid,Unpaid,Partially Paid',
            'amountPaid' => 'nullable|numeric|min:0',
        ]);

        \Illuminate\Support\Facades\Log::debug('OPD_BOOKING_VALIDATED');

        if ((float) $this->fee > 0 && $this->paymentStatus === 'Partially Paid') {
            $paid = (float) $this->amountPaid;
            if ($paid <= 0) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Enter an amount paid for Partially Paid.']);
                return;
            }
            if ($paid >= (float) $this->fee) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Amount paid must be less than total for Partially Paid.']);
                return;
            }
        }

        if ($this->isEditing) {
            $billing = app(BillingService::class);

            $consultation = Consultation::with(['bill.payments', 'service', 'doctor'])->findOrFail($this->editingId);

            $bill = $consultation->bill;
            $feeChanged = (float) $this->fee !== (float) $consultation->fee;

            if ($bill && $bill->payments()->exists() && $feeChanged) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Cannot change fee after payments. Use Billing to refund/adjust.',
                ]);
                return;
            }

            $newConsultationStatus = ($this->fee <= 0 || $this->paymentStatus === 'Paid') ? 'Paid' : 'Unpaid';

            $consultation->update([
                'service_id' => $this->selectedService,
                'doctor_id' => $this->selectedDoctor,
                'weight' => $this->weight,
                'temperature' => $this->temperature,
                'fee' => $this->fee,
                'notes' => $this->notes,
                'payment_status' => $newConsultationStatus,
                'payment_method' => $newConsultationStatus === 'Paid' ? $this->paymentMode : null,
            ]);

            if ($this->fee > 0) {
                $itemName = ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - Dr. ' . $consultation->doctor->full_name : '');
                $billData = [
                    'patient_id' => $consultation->patient_id,
                    'consultation_id' => $consultation->id,
                    'discount_amount' => $consultation->discount_amount ?? 0,
                    'tax_amount' => 0,
                    'payment_status' => $this->paymentStatus,
                    'paid_amount' => (float) $this->amountPaid,
                    'payment_method' => $this->paymentMode,
                    'notes' => 'Bill for ' . ($consultation->service?->name ?? 'OPD Consultation'),
                ];
                $items = [[
                    'name' => $itemName,
                    'type' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => (float) $this->fee,
                ]];

                if ($bill) {
                    DB::transaction(function () use ($bill, $items, $billData, $billing) {
                        $bill->update([
                            'discount_amount' => $billData['discount_amount'] ?? 0,
                            'tax_amount' => $billData['tax_amount'] ?? 0,
                            'notes' => $billData['notes'] ?? null,
                        ]);

                        $bill->items()->delete();
                        $subtotal = 0;
                        foreach ($items as $item) {
                            $totalPrice = $item['quantity'] * $item['unit_price'];
                            $bill->items()->create([
                                'item_name' => $item['name'],
                                'item_type' => $item['type'] ?? 'General',
                                'quantity' => $item['quantity'],
                                'unit_price' => $item['unit_price'],
                                'total_price' => $totalPrice,
                            ]);
                            $subtotal += $totalPrice;
                        }
                        $totalAmount = $subtotal - ($bill->discount_amount ?? 0) + ($bill->tax_amount ?? 0);
                        $bill->update(['subtotal' => $subtotal, 'total_amount' => $totalAmount]);

                        $billing->recalculatePaymentStatus($bill);
                    });
                } else {
                    $billing->createBill($billData, $items);
                }
            }

            $message = "Appointment updated successfully!";
            if ($shouldPrint) {
                $this->dispatch('print-op-slip', ['id' => $consultation->id]);
            }
        } else {
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
                    'payment_status' => ($this->fee <= 0 || $this->paymentStatus === 'Paid') ? 'Paid' : 'Unpaid',
                    'payment_method' => $this->paymentMode,
                    'bill_payment_status' => $this->paymentStatus,
                    'paid_amount' => (float) $this->amountPaid,
                    'notes' => $this->notes,
                ]);
                \Illuminate\Support\Facades\Log::info('OPD_BOOKING_SUCCESS', ['id' => $consultation->id]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('OPD_BOOKING_FAILED', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
                return;
            }
            
            $message = "Token #{$consultation->token_number} generated for {$this->selectedPatient->full_name}!";
            $this->lastConsultationId = $consultation->id;
            
            if ($shouldPrint) {
                $this->dispatch('print-op-slip', ['id' => $consultation->id]);
            }
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);


        $this->reset(['selectedPatient', 'selectedService', 'selectedDoctor', 'fee', 'notes', 'showBookingForm', 'isEditing', 'editingId', 'weight', 'temperature', 'paymentMode', 'paymentStatus', 'amountPaid', 'searchPatient', 'lastConsultationId', 'isFollowUp']);

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
        $this->dispatch('notify', ['type' => 'info', 'message' => 'Opening registration form...']);

        
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

    #[Computed]
    public function todayConsultationsQuery()
    {
        return Consultation::with(['patient', 'service', 'doctor.user', 'bill', 'patient.vitals' => fn($q) => $q->latest()->limit(1)])
            ->whereDate('consultation_date', now()->toDateString())
            ->when($this->selectedDoctor && !$this->showBookingForm, fn($query) => $query->where('doctor_id', $this->selectedDoctor));
    }

    #[Computed]
    public function stats()
    {
        $stats = $this->todayConsultationsQuery()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN payment_status = "Paid" THEN fee ELSE 0 END) as revenue
            ')
            ->first();
        
        return [
            'total' => (int) $stats->total,
            'pending' => (int) $stats->pending,
            'completed' => (int) $stats->completed,
            'cancelled' => (int) $stats->cancelled,
            'revenue' => (float) $stats->revenue,
        ];
    }

    public function render(OpdService $service)
    {
        $patients = [];
        if (strlen($this->searchPatient) >= 3) {
            $patients = Patient::search($this->searchPatient)
                ->with(['consultations' => fn($q) => $q->latest()->limit(1)])
                ->limit(5)
                ->get();
        }

        $doctors = Doctor::active()->with(['department', 'user'])->get();
        $services = \App\Models\Service::where('is_active', true)->where('category', 'OPD')->get();
        
        $todayConsultations = $this->todayConsultationsQuery->latest()->paginate(10);

        return view('livewire.counter.opd-booking', [
            'patients' => $patients,
            'doctors' => $doctors,
            'services' => $services,
            'todayConsultations' => $todayConsultations,
            'stats' => $this->stats
        ]);
    }
}
