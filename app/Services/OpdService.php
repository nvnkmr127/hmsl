<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OpdService
{
    protected $billingService;

    public function __construct(\App\Services\BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function getValidityDate($date = null, $serviceId = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        
        $days = \App\Models\Setting::get('opd_validity_days', 7);
        if ($serviceId) {
            $service = \App\Models\Service::find($serviceId);
            if ($service && $service->validity_days > 0) {
                $days = $service->validity_days;
            }
        }
        
        return $date->addDays((int)$days)->toDateString();
    }

    /**
     * Centralized logic to determine booking details (follow-up, review, fees, etc.)
     */
    public function calculateBookingDetails(\App\Models\Patient $patient, ?int $serviceId = null, ?int $doctorId = null, ?string $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateStr = $date->toDateString();

        $latestConsultation = Consultation::where('patient_id', $patient->id)
            ->with(['doctor', 'service'])
            ->where('status', '!=', 'Cancelled')
            ->latest('consultation_date')
            ->first();

        $isReview = false;
        $isFollowUp = false;
        $suggestedFee = 0;
        
        // 1. Check for REVIEW (within service/global validity of previous visit)
        if ($latestConsultation) {
            $serviceValidity = $latestConsultation->service?->validity_days ?? \App\Models\Setting::get('opd_validity_days', 7);
            $daysSinceLastVisit = Carbon::parse($latestConsultation->consultation_date)->diffInDays($date);
            
            if ($daysSinceLastVisit <= $serviceValidity) {
                $isReview = true;
                $isFollowUp = true;
                $suggestedFee = 0;
            }
        }

        // 2. Check for FOLLOW-UP (any active validity)
        if (!$isReview) {
            $hasActiveValidity = Consultation::where('patient_id', $patient->id)
                ->where('status', '!=', 'Cancelled')
                ->where('valid_upto', '>=', $dateStr)
                ->exists();
            
            if ($hasActiveValidity) {
                $isFollowUp = true;
                $suggestedFee = 0;
            }
        }

        // 3. Determine Fee if not Follow-up/Review
        if (!$isFollowUp) {
            if ($serviceId) {
                $service = \App\Models\Service::find($serviceId);
                $suggestedFee = $service ? $service->price : 0;
            } elseif ($doctorId) {
                $doctor = Doctor::find($doctorId);
                $suggestedFee = $doctor ? $doctor->consultation_fee : \App\Models\Setting::get('consultation_fee_default', 500);
            } else {
                $suggestedFee = \App\Models\Setting::get('consultation_fee_default', 500);
            }
        }

        $validUpto = $this->getValidityDate($dateStr, $serviceId ?: ($latestConsultation?->service_id));

        return [
            'is_review' => $isReview,
            'is_follow_up' => $isFollowUp,
            'suggested_fee' => (float)$suggestedFee,
            'valid_upto' => $validUpto,
            'latest_consultation' => $latestConsultation,
        ];
    }

    public function generateToken(?int $doctorId, $date = null, ?int $serviceId = null)
    {
        $date = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        
        $query = Consultation::whereDate('consultation_date', $date);

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        } else {
            $query->whereNull('doctor_id')->where('service_id', $serviceId);
        }

        // EXCLUSIVE LOCK: Prevent other concurrent requests from reading the same Max for their increment
        $lastToken = $query->lockForUpdate()->max('token_number');
            
        return ($lastToken ?: 0) + 1;
    }

    public function bookAppointment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $billPaymentStatus = $data['bill_payment_status'] ?? null;
            $paidAmount = isset($data['paid_amount']) ? (float) $data['paid_amount'] : 0;
            unset($data['bill_payment_status'], $data['paid_amount']);

            $consultationDate = isset($data['consultation_date']) ? Carbon::parse($data['consultation_date']) : now();
            $data['consultation_date'] = $consultationDate->toDateString();

            // 1. ATOMIC DUPLICATE CHECK: Prevent double-booking race condition
            // Only block if there's a PENDING or IN-PROGRESS booking for the same service today
            $exists = Consultation::where('patient_id', $data['patient_id'])
                ->where('service_id', $data['service_id'])
                ->whereDate('consultation_date', $data['consultation_date'])
                ->whereIn('status', ['Pending', 'In Progress'])
                ->when(isset($data['doctor_id']), fn($q) => $q->where('doctor_id', $data['doctor_id']))
                ->when(isset($data['id']), fn($q) => $q->where('id', '!=', $data['id']))
                ->sharedLock()
                ->exists();

            if ($exists) {
                throw new \Exception('Patient already has an active (Pending/In-Progress) booking for this service on this date.');
            }

            // 1.1 DOCTOR-SERVICE COMPATIBILITY CHECK
            if (isset($data['doctor_id']) && isset($data['service_id'])) {
                $doctor = Doctor::find($data['doctor_id']);
                $service = \App\Models\Service::find($data['service_id']);
                
                if ($doctor && $service && $service->department_id && $doctor->department_id !== $service->department_id) {
                    $doctorDept = $doctor->department?->name ?? 'Doctor\'s Dept';
                    $serviceDept = $service->department?->name ?? 'Service\'s Dept';
                    throw new \Exception("Department mismatch: This service belongs to {$serviceDept}, but Dr. {$doctor->full_name} is in {$doctorDept}.");
                }
            }
            
            // 2. SAFE TOKEN GENERATION: Within the same transaction with exclusive locking
            $data['token_number'] = $this->generateToken(
                $data['doctor_id'] ?? null, 
                $data['consultation_date'],
                $data['service_id'] ?? null
            );

            $service = isset($data['service_id']) ? \App\Models\Service::find($data['service_id']) : null;
            $validityDays = ($service && $service->validity_days > 0) 
                ? $service->validity_days 
                : \App\Models\Setting::get('opd_validity_days', 7);

            $data['valid_upto'] = $data['valid_upto'] ?? $consultationDate->copy()->addDays((int)$validityDays)->toDateString();

            // 3. STRICT REVENUE PROTECTION: Ensure fees are never zero due to missing relations
            // Except for follow-up visits within validity period
            if (!isset($data['fee']) || $data['fee'] <= 0) {
                $hasRecentVisit = false;
                
                if ($service && $service->validity_days > 0) {
                    $hasRecentVisit = Consultation::where('patient_id', $data['patient_id'])
                        ->where('status', '!=', 'Cancelled')
                        ->where('service_id', $service->id)
                        ->whereDate('consultation_date', '>=', $consultationDate->copy()->subDays($service->validity_days))
                        ->exists();
                } else {
                    $hasRecentVisit = Consultation::where('patient_id', $data['patient_id'])
                        ->where('status', '!=', 'Cancelled')
                        ->where('valid_upto', '>=', $data['consultation_date'])
                        ->exists();
                }

                if ($hasRecentVisit) {
                    $data['fee'] = 0;
                    $data['visit_type'] = $data['visit_type'] ?? 'Follow-up';
                } elseif ($service) {
                    $data['fee'] = $service->price;
                } elseif (isset($data['doctor_id'])) {
                    $doctor = Doctor::find($data['doctor_id']);
                    if (!$doctor) throw new \Exception('Requested Doctor record no longer exists.');
                    $data['fee'] = $doctor->consultation_fee;
                }
            }

            if (($data['fee'] ?? 0) < 0) {
                throw new \Exception('Could not determine a valid fee for this consultation.');
            }
            
            // Still enforce fee if no recent visit and no price found
            if ($data['fee'] == 0) {
                $hasRecentVisit = false;
                if ($service && $service->validity_days > 0) {
                    $hasRecentVisit = Consultation::where('patient_id', $data['patient_id'])
                        ->where('status', '!=', 'Cancelled')
                        ->where('service_id', $service->id)
                        ->whereDate('consultation_date', '>=', $consultationDate->copy()->subDays($service->validity_days))
                        ->exists();
                } else {
                    $hasRecentVisit = Consultation::where('patient_id', $data['patient_id'])
                        ->where('status', '!=', 'Cancelled')
                        ->where('valid_upto', '>=', $data['consultation_date'])
                        ->exists();
                }

                if (!$hasRecentVisit) {
                    throw new \Exception('Consultation fee must be greater than zero for new visits.');
                }
            }

            $consultation = Consultation::create($data);

            \Illuminate\Support\Facades\Log::info('OPD_BOOKING_CREATED', [
                'id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'token' => $consultation->token_number,
                'user_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            if ($consultation->fee > 0) {
                $consultation->loadMissing(['service', 'doctor']);

                $status = in_array($billPaymentStatus, ['Paid', 'Unpaid', 'Partially Paid'], true)
                    ? $billPaymentStatus
                    : ($consultation->payment_status === 'Paid' ? 'Paid' : 'Unpaid');

                $this->billingService->createBill([
                    'patient_id' => $consultation->patient_id,
                    'consultation_id' => $consultation->id,
                    'discount_amount' => $consultation->discount_amount ?? 0,
                    'payment_status' => $status,
                    'paid_amount' => $paidAmount,
                    'payment_method' => $consultation->payment_method ?? 'Cash',
                    'notes' => 'Bill for ' . ($consultation->service?->name ?? 'OPD Consultation'),
                ], [
                    [
                        'name' => ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - Dr. ' . $consultation->doctor->full_name : ''),
                        'type' => 'Consultation',
                        'quantity' => 1,
                        'unit_price' => $consultation->fee
                    ]
                ]);
            }

            event(new \App\Events\OPD\AppointmentBooked($consultation->load(['patient', 'doctor', 'service'])));

            return $consultation;
        });
    }

    public function getDailyQueue(?int $doctorId, $date = null)
    {
        $date = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        
        return Consultation::with(['patient', 'doctor.user', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('consultation_date', $date)
            ->orderBy('token_number')
            ->get();
    }

    public function updateStatus(Consultation $consultation, string $status)
    {
        $consultation->update(['status' => $status]);
        
        \Illuminate\Support\Facades\Log::info('OPD_STATUS_UPDATED', [
            'id' => $consultation->id,
            'status' => $status,
            'user_id' => Auth::id()
        ]);

        if ($status === 'Completed') {
            event(new \App\Events\OPD\ConsultationCompleted($consultation->load(['patient', 'doctor.user'])));
        }
        
        return $consultation;
    }

    public function markAsPaid(Consultation $consultation, $method = 'Cash')
    {
        if ($consultation->payment_status === 'Paid') {
            return $consultation;
        }

        return DB::transaction(function () use ($consultation, $method) {
            $consultation->update([
                'payment_status' => 'Paid',
                'payment_method' => $method
            ]);

            \Illuminate\Support\Facades\Log::info('OPD_PAYMENT_RECEIVED', [
                'id' => $consultation->id,
                'method' => $method,
                'amount' => $consultation->fee,
                'user_id' => Auth::id()
            ]);

            // If no bill exists for this consultation, create one
            $existingBill = Bill::where('consultation_id', $consultation->id)->first();
            if (!$existingBill) {
                $this->billingService->createBill([
                    'patient_id' => $consultation->patient_id,
                    'consultation_id' => $consultation->id,
                    'discount_amount' => $consultation->discount_amount,
                    'tax_amount' => 0,
                    'payment_status' => 'Paid',
                    'payment_method' => $method,
                    'notes' => 'Settlement for ' . ($consultation->service?->name ?? 'OPD Consultation'),
                ], [
                    [
                        'name' => ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - Dr. ' . $consultation->doctor->full_name : ''),
                        'type' => 'Consultation',
                        'quantity' => 1,
                        'unit_price' => $consultation->fee
                    ]
                ]);
            } else {
                // If bill exists but is unpaid, mark it as paid
                if ($existingBill->payment_status !== 'Paid') {
                    $this->billingService->markAsPaid($existingBill, $method);
                }
            }

            return $consultation;
        });
    }
}
