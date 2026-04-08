<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Doctor;
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

    public function getValidityDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $days = \App\Models\Setting::get('opd_validity_days', 7);
        return $date->addDays((int)$days)->toDateString();
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
            $exists = Consultation::where('patient_id', $data['patient_id'])
                ->where('service_id', $data['service_id'])
                ->whereDate('consultation_date', $data['consultation_date'])
                ->where('status', '!=', 'Cancelled')
                ->sharedLock() // Shared lock ensures no concurrent inserts of the same row
                ->exists();

            if ($exists) {
                throw new \Exception('Patient already has an active booking for this service on this date.');
            }
            
            // 2. SAFE TOKEN GENERATION: Within the same transaction with exclusive locking
            $data['token_number'] = $this->generateToken(
                $data['doctor_id'] ?? null, 
                $data['consultation_date'],
                $data['service_id'] ?? null
            );

            $validityDays = \App\Models\Setting::get('opd_validity_days', 7);
            $data['valid_upto'] = $data['valid_upto'] ?? $consultationDate->copy()->addDays((int)$validityDays)->toDateString();

            // 3. STRICT REVENUE PROTECTION: Ensure fees are never zero due to missing relations
            // Except for follow-up visits within validity period
            if (!isset($data['fee']) || $data['fee'] <= 0) {
                $hasRecentVisit = Consultation::where('patient_id', $data['patient_id'])
                    ->where('status', '!=', 'Cancelled')
                    ->where('valid_upto', '>=', $data['consultation_date'])
                    ->exists();

                if ($hasRecentVisit) {
                    $data['fee'] = 0;
                } elseif (isset($data['service_id'])) {
                    $service = \App\Models\Service::find($data['service_id']);
                    if (!$service) throw new \Exception('Requested Service record no longer exists.');
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
                $hasRecentVisit = Consultation::where('patient_id', $data['patient_id'])
                    ->where('status', '!=', 'Cancelled')
                    ->where('valid_upto', '>=', $data['consultation_date'])
                    ->exists();
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
                    'tax_amount' => 0,
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
            if (!$consultation->bill()->exists()) {
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
            }

            return $consultation;
        });
    }
}
