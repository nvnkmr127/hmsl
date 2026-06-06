<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Bill;
use App\Models\PatientVital;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OpdService
{
    protected $billingService;
    const EMERGENCY_FEE = 500;
    const NEWBORN_FREE_DAYS = 9;
    const POST_DISCHARGE_FREE_DAYS = 7;

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
     * Determine if current time falls under Emergency/After-hours pricing.
     * Rules: 10 PM - 9 AM daily, Sunday 6 PM onwards.
     */
    public function isEmergencyPricing(?Carbon $time = null): bool
    {
        $time = $time ?? now();
        $hour = $time->hour;
        $day = $time->dayOfWeek;

        // Daily 10 PM (22:00) to 9 AM (09:00)
        if ($hour >= 22 || $hour < 9) {
            return true;
        }

        // Sunday 6 PM (18:00) onwards
        if ($day === Carbon::SUNDAY && $hour >= 18) {
            return true;
        }

        return false;
    }

    /**
     * Check if a patient has any active validity for a revisit.
     * Considers service-specific validity if service has its own validity period.
     */
    public function hasActiveValidity(int $patientId, ?int $serviceId = null, $date = null): bool
    {
        $date = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        
        // We look for ANY non-cancelled consultation that is still valid.
        // This makes the 7-day (or service-specific) validity global for the patient.
        $query = Consultation::where('patient_id', $patientId)
            ->where('status', '!=', 'Cancelled')
            ->where('valid_upto', '>=', $date);
        return $query->exists();
    }

    /**
     * Check if newborn is eligible for free visits (within 7 days of birth and attended by our doctor).
     */
    public function isEligibleNewborn(\App\Models\Patient $patient, $date = null): bool
    {
        if (!$patient->is_delivery_attended) return false;
        if (!$patient->date_of_birth) return false;

        $date = $date ? Carbon::parse($date) : now();
        $dob = Carbon::parse($patient->date_of_birth)->startOfDay();
        
        // Return true if current date is within NEWBORN_FREE_DAYS of DOB
        return $dob->diffInDays($date->copy()->startOfDay()) <= self::NEWBORN_FREE_DAYS;
    }

    /**
     * Check if a discharged inpatient is within their 7-day free OP window.
     * Returns true only if the visit date is NOT a Sunday.
     * (Sunday visits are charged normally — they simply don't get the free benefit.)
     */
    public function isPostDischargeBenefit(\App\Models\Patient $patient, $date = null): bool
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();

        // Sundays: patient must pay normally even within the 7-day window
        if ($date->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }

        $latestDischarge = $patient->admissions()
            ->where('status', 'Discharged')
            ->whereNotNull('discharge_date')
            ->latest('discharge_date')
            ->first();

        if (!$latestDischarge) return false;

        $dischargeDate = Carbon::parse($latestDischarge->discharge_date)->startOfDay();
        $windowEnd     = $dischargeDate->copy()->addDays(self::POST_DISCHARGE_FREE_DAYS);

        return $date->between($dischargeDate, $windowEnd);
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
        $isEmergency = $this->isEmergencyPricing();
        $isNewbornBenefit = $this->isEligibleNewborn($patient, $dateStr);
        $isPostDischargeBenefit = $this->isPostDischargeBenefit($patient, $dateStr);

        $activeValidConsultation = Consultation::where('patient_id', $patient->id)
            ->where('status', '!=', 'Cancelled')
            ->where('valid_upto', '>=', $dateStr)
            ->orderBy('valid_upto', 'desc')
            ->first();

        if ($activeValidConsultation) {
            $calculatedValidUpto = Carbon::parse($activeValidConsultation->valid_upto)->toDateString();
        } else if ($isNewbornBenefit) {
            $calculatedValidUpto = $date->copy()->addDays(9)->toDateString();
        } else {
            $calculatedValidUpto = $this->getValidityDate($dateStr, $serviceId ?: ($latestConsultation?->service_id));
        }

        if ($isNewbornBenefit) {
            return [
                'is_review'               => false,
                'is_follow_up'            => true,
                'suggested_fee'           => 0.0,
                'is_emergency'            => false,
                'is_newborn_benefit'      => true,
                'is_post_discharge'       => false,
                'valid_upto'              => $calculatedValidUpto,
                'latest_consultation'     => $latestConsultation,
            ];
        }

        if ($isPostDischargeBenefit) {
            return [
                'is_review'               => false,
                'is_follow_up'            => true,
                'suggested_fee'           => 0.0,
                'is_emergency'            => false,
                'is_newborn_benefit'      => false,
                'is_post_discharge'       => true,
                'valid_upto'              => $calculatedValidUpto,
                'latest_consultation'     => $latestConsultation,
            ];
        }

        if ($isEmergency) {
            return [
                'is_review'               => false,
                'is_follow_up'            => false,
                'suggested_fee'           => (float) self::EMERGENCY_FEE,
                'is_emergency'            => true,
                'is_newborn_benefit'      => false,
                'is_post_discharge'       => false,
                'valid_upto'              => $calculatedValidUpto,
                'latest_consultation'     => $latestConsultation,
            ];
        }

        $isReview = false;
        $isFollowUp = $this->hasActiveValidity($patient->id, $serviceId, $dateStr);
        $suggestedFee = 0;
        
        // 1. A visit is a "Review" if the MOST RECENT consultation is still valid.
        if ($latestConsultation) {
            if (Carbon::parse($latestConsultation->valid_upto)->startOfDay()->greaterThanOrEqualTo($date->copy()->startOfDay())) {
                $isReview = true;
                $isFollowUp = true; // Safety
            }
        }

        // 2. Determine Fee
        if ($isFollowUp) {
            $suggestedFee = 0;
        } else {
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

        return [
            'is_review'           => $isReview,
            'is_follow_up'        => $isFollowUp,
            'suggested_fee'       => (float)$suggestedFee,
            'is_emergency'        => false,
            'is_newborn_benefit'  => false,
            'is_post_discharge'   => false,
            'valid_upto'          => $calculatedValidUpto,
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
                    throw new \Exception("Department mismatch: This service belongs to {$serviceDept}, but {$doctor->full_name} is in {$doctorDept}.");
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

            $patient = \App\Models\Patient::find($data['patient_id']);
            $isNewbornBenefit       = $patient ? $this->isEligibleNewborn($patient, $data['consultation_date']) : false;
            $isPostDischargeBenefit = $patient ? $this->isPostDischargeBenefit($patient, $data['consultation_date']) : false;
            $isEmergency = $this->isEmergencyPricing();
            
            $isFollowUp = $this->hasActiveValidity($data['patient_id'], $data['service_id'] ?? null, $data['consultation_date']);

            if (!isset($data['valid_upto'])) {
                $activeValidConsultation = Consultation::where('patient_id', $data['patient_id'])
                    ->where('status', '!=', 'Cancelled')
                    ->where('valid_upto', '>=', $data['consultation_date'])
                    ->orderBy('valid_upto', 'desc')
                    ->first();
                
                if ($activeValidConsultation) {
                    $data['valid_upto'] = Carbon::parse($activeValidConsultation->valid_upto)->toDateString();
                } else {
                    if ($isNewbornBenefit) {
                        $data['valid_upto'] = $consultationDate->copy()->addDays(9)->toDateString();
                    } else {
                        $data['valid_upto'] = $consultationDate->copy()->addDays((int)$validityDays)->toDateString();
                    }
                }
            }

            // Priority logic for fee assignment
            if ($isNewbornBenefit) {
                $data['fee']        = 0;
                $data['visit_type'] = $data['visit_type'] ?? 'Newborn Followup';
            } elseif ($isPostDischargeBenefit) {
                $data['fee']        = 0;
                $data['visit_type'] = $data['visit_type'] ?? 'Post-Discharge Follow-up';
                // Force follow-up flag so revenue-protection check below is skipped
                $isFollowUp = true;
            } elseif ($isEmergency) {
                $data['fee']        = self::EMERGENCY_FEE;
                $data['visit_type'] = $data['visit_type'] ?? 'Emergency';
            } elseif ($isFollowUp) {
                $data['fee']        = 0;
                $data['visit_type'] = $data['visit_type'] ?? 'Follow-up';
            }

            // 4. STRICT REVENUE PROTECTION: Ensure fees are never zero due to missing relations
            // Except for follow-up visits within validity period or post-discharge benefit
            if (!$isNewbornBenefit && !$isPostDischargeBenefit && !$isEmergency && !$isFollowUp && (!isset($data['fee']) || $data['fee'] <= 0)) {
                if ($service) {
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
            if ($data['fee'] == 0 && !$isNewbornBenefit && !$isPostDischargeBenefit) {
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

            $weight = $consultation->weight;
            $height = $consultation->height;
            $temperature = $consultation->temperature;

            $hasAnyVitals = $weight !== null || $height !== null || $temperature !== null;
            if ($hasAnyVitals) {
                $bmi = null;
                if ($weight !== null && $height !== null && (float) $height > 0) {
                    $heightInMeters = (float) $height / 100;
                    $bmi = round(((float) $weight) / ($heightInMeters * $heightInMeters), 1);
                }

                PatientVital::updateOrCreate(
                    [
                        'patient_id' => $consultation->patient_id,
                        'consultation_id' => $consultation->id,
                    ],
                    [
                        'recorded_by' => Auth::id(),
                        'weight' => $weight,
                        'height' => $height,
                        'bmi' => $bmi,
                        'temperature' => $temperature,
                    ]
                );
            }

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
                        'name' => ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - ' . $consultation->doctor->full_name : ''),
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
                        'name' => ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - ' . $consultation->doctor->full_name : ''),
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

    public function updateAppointment(Consultation $consultation, array $data)
    {
        return DB::transaction(function () use ($consultation, $data) {
            $fee = isset($data['fee']) ? (float)$data['fee'] : $consultation->fee;
            
            // 1. Check if we need to adjust payments and bill
            if ($consultation->bill) {
                $bill = $consultation->bill;
                $payments = $bill->payments;

                if ((float)$fee !== (float)$consultation->fee || isset($data['bill_payment_status'])) {
                    if ($payments->count() > 1) {
                        if ((float)$fee !== (float)$consultation->fee) {
                            throw new \Exception('Cannot change fee: Multiple payments have already been recorded for this visit.');
                        }
                    } else {
                        // 0 or 1 payment exists, we can adjust it.
                        $payment = $payments->first();
                        $paymentStatus = $data['bill_payment_status'] ?? ($consultation->payment_status === 'Paid' ? 'Paid' : 'Unpaid');
                        $paidAmount = isset($data['paid_amount']) ? (float)$data['paid_amount'] : ($paymentStatus === 'Paid' ? $fee : 0);

                        if ($fee <= 0) {
                            // If fee becomes 0, delete the bill and its payments
                            $bill->items()->delete();
                            $bill->payments()->delete();
                            $bill->delete();
                        } else {
                            // Adjust payments based on payment status
                            if ($paymentStatus === 'Paid') {
                                if ($payment) {
                                    $payment->update([
                                        'amount' => $fee,
                                        'method' => $data['payment_method'] ?? $payment->method,
                                    ]);
                                } else {
                                    $this->billingService->recordPayment($bill, $fee, $data['payment_method'] ?? 'Cash', 'payment');
                                }
                            } elseif ($paymentStatus === 'Partially Paid') {
                                if ($paidAmount > 0) {
                                    if ($payment) {
                                        $payment->update([
                                            'amount' => $paidAmount,
                                            'method' => $data['payment_method'] ?? $payment->method,
                                        ]);
                                    } else {
                                        $this->billingService->recordPayment($bill, $paidAmount, $data['payment_method'] ?? 'Cash', 'payment');
                                    }
                                } else {
                                    if ($payment) {
                                        $payment->delete();
                                    }
                                }
                            } else { // Unpaid
                                if ($payment) {
                                    $payment->delete();
                                }
                            }
                        }
                    }
                }
            } else {
                // If there was no bill (because fee was 0) but now fee > 0, we create the bill!
                if ($fee > 0) {
                    $consultation->loadMissing(['service', 'doctor']);
                    $paymentStatus = $data['bill_payment_status'] ?? ($consultation->payment_status === 'Paid' ? 'Paid' : 'Unpaid');
                    $paidAmount = isset($data['paid_amount']) ? (float)$data['paid_amount'] : ($paymentStatus === 'Paid' ? $fee : 0);

                    $this->billingService->createBill([
                        'patient_id' => $consultation->patient_id,
                        'consultation_id' => $consultation->id,
                        'discount_amount' => $consultation->discount_amount ?? 0,
                        'payment_status' => $paymentStatus,
                        'paid_amount' => $paidAmount,
                        'payment_method' => $data['payment_method'] ?? 'Cash',
                        'notes' => 'Bill for ' . ($consultation->service?->name ?? 'OPD Consultation'),
                    ], [
                        [
                            'name' => ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - ' . $consultation->doctor->full_name : ''),
                            'type' => 'Consultation',
                            'quantity' => 1,
                            'unit_price' => $fee
                        ]
                    ]);
                }
            }

            // Remove payment fields before updating Consultation model
            $consultationData = $data;
            unset($consultationData['bill_payment_status'], $consultationData['paid_amount']);

            // 2. Update Consultation
            $consultation->update($consultationData);

            // 3. Update Vitals
            $weight = $data['weight'] ?? $consultation->weight;
            $height = $data['height'] ?? $consultation->height;
            $temperature = $data['temperature'] ?? $consultation->temperature;

            if ($weight !== null || $height !== null || $temperature !== null) {
                $bmi = null;
                if ($weight !== null && $height !== null && (float) $height > 0) {
                    $heightInMeters = (float) $height / 100;
                    $bmi = round(((float) $weight) / ($heightInMeters * $heightInMeters), 1);
                }

                PatientVital::updateOrCreate(
                    [
                        'patient_id' => $consultation->patient_id,
                        'consultation_id' => $consultation->id,
                    ],
                    [
                        'recorded_by' => Auth::id(),
                        'weight' => $weight,
                        'height' => $height,
                        'bmi' => $bmi,
                        'temperature' => $temperature,
                    ]
                );
            }

            // 4. Update Bill details if bill exists
            $consultation->refresh();
            if ($consultation->bill) {
                $bill = $consultation->bill;
                
                // Reload relations to get new names if IDs changed
                $consultation->load(['service', 'doctor']);
                
                $itemName = ($consultation->service?->name ?? 'Consultation Fee') . ($consultation->doctor ? ' - ' . $consultation->doctor->full_name : '');
                
                // Update bill items
                $bill->items()->delete();
                $bill->items()->create([
                    'item_name' => $itemName,
                    'item_type' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => $fee,
                    'total_price' => $fee,
                ]);

                // Update subtotal, calculate tax based on settings, and update total amount on bill before recalculating payments
                $taxRate = (float) \App\Models\Setting::get('tax_rate', 0);
                $taxAmount = $taxRate > 0 ? ($fee * ($taxRate / 100)) : 0;
                $totalAmount = $fee - ($bill->discount_amount ?? 0) + $taxAmount;

                $bill->update([
                    'subtotal' => $fee,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);

                // Recalculate bill
                $this->billingService->recalculatePaymentStatus($bill);
            }

            return $consultation;
        });
    }
}
