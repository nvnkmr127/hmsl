<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Setting;
use App\Models\IpdMedicationChart;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class IpdService
{
    protected BillingService $billingService;
    protected SequenceService $sequenceService;

    public function __construct(BillingService $billingService, SequenceService $sequenceService)
    {
        $this->billingService = $billingService;
        $this->sequenceService = $sequenceService;
    }

    public function generateAdmissionNumber(): string
    {
        $prefix = \App\Models\Setting::get('admission_prefix', 'ADM');
        $scope = now()->format('Y');

        $seq = $this->sequenceService->next('admission', $scope, function () use ($scope) {
            $max = 0;
            $existing = Admission::query()
                ->where('admission_number', 'like', '%-' . $scope . '-%')
                ->get(['admission_number']);

            foreach ($existing as $admission) {
                $parts = explode('-', (string) $admission->admission_number);
                $n = (int) end($parts);
                $max = max($max, $n);
            }

            return $max;
        });

        return sprintf('%s-%s-%05d', $prefix, $scope, $seq);
    }

    public function admitPatient(array $data): Admission
    {
        return DB::transaction(function () use ($data) {
            $data['admission_number'] = $this->generateAdmissionNumber();
            $data['created_by'] = Auth::id();
            $data['status'] = 'Admitted';
            $data['admission_date'] = $data['admission_date'] ?? now()->toDateTimeString();

            // 1. DUPLICATE ADMISSION CHECK
            $activeAdmission = Admission::where('patient_id', $data['patient_id'])
                ->where('status', 'Admitted')
                ->first();

            if ($activeAdmission) {
                throw new \RuntimeException("Patient is already admitted (Admission: {$activeAdmission->admission_number}). Discharge current admission first.");
            }

            // 2. BED AVAILABILITY WITH LOCK
            $bed = Bed::query()->lockForUpdate()->findOrFail($data['bed_id']);
            if (!$bed->is_available) {
                throw new \RuntimeException('Selected bed is no longer available.');
            }
            $bed->update(['is_available' => false]);

            $admission = Admission::create($data);

            if (isset($data['weight']) || isset($data['height'])) {
                \App\Models\PatientVital::create([
                    'patient_id' => $admission->patient_id,
                    'admission_id' => $admission->id,
                    'weight' => $data['weight'] ?? null,
                    'height' => $data['height'] ?? null,
                    'recorded_by' => Auth::id(),
                ]);
            }

            event(new \App\Events\IPD\PatientAdmitted($admission->load(['patient', 'bed.ward', 'doctor.user'])));

            return $admission;
        });
    }

    public function dischargePatient(Admission $admission, ?string $notes = null): Admission
    {
        return DB::transaction(function () use ($admission, $notes) {
            // 1. REFRESH & LOCK FOR UPDATE
            $admission = Admission::query()->lockForUpdate()->find($admission->id);
            if ($admission->status === 'Discharged') {
                throw new \RuntimeException('Patient is already discharged.');
            }

            // 2. DISCHARGE SUMMARY CHECK (MANDATORY & FINALIZED)
            $summary = $admission->dischargeSummary;
            if (!$summary || !$summary->is_finalized) {
                throw new \RuntimeException('A Finalized Discharge Summary is mandatory before discharge.');
            }

            // 3. CLINICAL SAFETY CHECKS (PENDING ORDERS/MEDS)
            $pendingOrders = \App\Models\LabOrder::where('admission_id', $admission->id)
                ->whereNotIn('status', ['Completed', 'Cancelled', 'Rejected'])
                ->count();
            if ($pendingOrders > 0) {
                throw new \RuntimeException("Cannot discharge. There are {$pendingOrders} pending lab orders that must be completed or cancelled.");
            }

            $activeMeds = \App\Models\IpdMedicationChart::where('admission_id', $admission->id)
                ->where('status', 'Active')
                ->count();
            if ($activeMeds > 0) {
                throw new \RuntimeException("Cannot discharge. There are {$activeMeds} active medications. Stop or complete all medications first.");
            }

            // 4. PRE-RECALCULATE FINAL BILL (To catch last-minute charges)
            if (Schema::hasColumn('bills', 'admission_id')) {
                 // Update discharge date temporarily for billing calculation
                $originalDischargeDate = $admission->discharge_date;
                $admission->discharge_date = now();
                
                $billItems = $this->buildFinalBillItems($admission);
                $this->billingService->upsertAdmissionFinalBill($admission, $billItems);
                
                $admission->refresh();
            }

            // 5. BILL SETTLEMENT CHECK
            $bill = $admission->finalBill;
            if ($bill && $bill->payment_status !== 'Paid') {
                $balance = number_format($bill->balance_amount, 2);
                throw new \RuntimeException("Cannot discharge. Final bill has a pending balance of {$balance}. Settle the bill first.");
            }

            // 6. EXECUTE DISCHARGE
            $admission->update([
                'discharge_date' => now(),
                'status' => 'Discharged',
                'notes' => $notes ?: $admission->notes,
                'discharged_by' => Auth::id(), // Audit Trail
            ]);

            // 7. BED RELEASE
            $admission->bed()->update(['is_available' => true]);

            event(new \App\Events\IPD\PatientDischarged($admission->load(['patient', 'bed.ward', 'doctor.user'])));

            return $admission;
        });
    }

    public function buildFinalBillItems(Admission $admission): array
    {
        $admission->loadMissing(['bed.ward', 'bed', 'labOrders.labTest', 'ipdMedications']);

        $items = [];

        $admittedAt = $admission->admission_date ? Carbon::parse($admission->admission_date) : now();
        $dischargedAt = $admission->discharge_date ? Carbon::parse($admission->discharge_date) : now();
        $stayHours = max(0, $admittedAt->diffInHours($dischargedAt));
        $stayDays = max(1, (int) ceil($stayHours / 24));

        $ward = $admission->bed?->ward;
        $bed = $admission->bed;
        $dailyCharge = (float) ($bed?->per_day_charge ?? $ward?->daily_charge ?? 0);

        if ($dailyCharge > 0) {
            $items[] = [
                'name' => ($ward?->name ?? 'Ward') . ' - ' . ($bed?->bed_number ?? 'Bed') . ' (' . $stayDays . ' days)',
                'type' => 'IPD',
                'quantity' => $stayDays,
                'unit_price' => $dailyCharge,
            ];
        }

        $doctor = $admission->doctor;
        if ($doctor) {
            $visitCharge = (float) ($doctor->consultation_charge ?? Setting::get('ipd_daily_doctor_charge', 500));
            // EVIDENCE-BASED BILLING: Count distinct days of doctor rounds
            $doctorVisitDays = $admission->ipdNotes()
                ->where('note_type', 'Doctor')
                ->selectRaw('COUNT(DISTINCT DATE(note_date)) as days')
                ->value('days');
            
            if ($doctorVisitDays > 0) {
                $items[] = [
                    'name' => 'Doctor Visit Charges - Dr. ' . $doctor->full_name . ' (' . $doctorVisitDays . ' days)',
                    'type' => 'Consultation',
                    'quantity' => $doctorVisitDays,
                    'unit_price' => $visitCharge,
                ];
            }
        }

        foreach ($admission->labOrders as $order) {
            $price = (float) ($order->labTest?->price ?? 0);
            if ($price <= 0) {
                continue;
            }

            $items[] = [
                'name' => $order->labTest?->name ?? 'Lab Test',
                'type' => 'Lab',
                'quantity' => 1,
                'unit_price' => $price,
                'source_type' => \App\Models\LabOrder::class,
                'source_id' => $order->id,
            ];
        }

        foreach ($admission->ipdMedications as $rx) {
            $medicine = $rx->medicine;
            if (!$medicine) continue;

            $unitPrice = (float) ($medicine->selling_price ?? $medicine->price ?? 0);
            if ($unitPrice <= 0) continue;

            // EVIDENCE-BASED BILLING: Count actual administrations from MAR
            $administeredQty = \App\Models\IpdMedicationAdministration::where('ipd_medication_chart_id', $rx->id)
                ->where('status', 'Given')
                ->count();

            if ($administeredQty > 0) {
                $items[] = [
                    'name' => $rx->medicine_name . ($rx->dosage ? ' - ' . $rx->dosage : ''),
                    'type' => 'Pharmacy',
                    'quantity' => $administeredQty,
                    'unit_price' => $unitPrice,
                    'source_type' => \App\Models\IpdMedicationChart::class,
                    'source_id' => $rx->id,
                ];
            }
        }

        return $items;
    }

    protected function calculateMedicationQuantity(IpdMedicationChart $med): int
    {
        $start = $med->start_date;
        $end = $med->end_date ?? ($med->stopped_at ?? now());

        $days = max(1, (int) $start->diffInDays($end));

        $freqMultiplier = match ($med->frequency) {
            'OD', 'Once daily' => 1,
            'BD', 'Twice daily' => 2,
            'TDS', 'Three times daily' => 3,
            'QID', 'Four times daily' => 4,
            'SOS', 'PRN' => 1,
            default => 1,
        };

        return $days * $freqMultiplier;
    }

    public function ensureFinalBill(Admission $admission): void
    {
        if (!Schema::hasColumn('bills', 'admission_id')) {
            return;
        }

        $billItems = $this->buildFinalBillItems($admission->fresh());
        $this->billingService->upsertAdmissionFinalBill($admission, $billItems);
    }

    public function getDischargeDetails(Admission $admission): Admission
    {
        $relations = [
            'patient',
            'doctor.user',
            'bed.ward',
            'vitals',
            'ipdMedications',
            'ipdNotes',
            'ipdVitals',
            'labOrders.labTest',
            'diagnoses',
            'dischargeSummary.medications',
        ];

        if (Schema::hasColumn('bills', 'admission_id')) {
            $relations[] = 'finalBill.items';
        }

        return $admission->load($relations);
    }
}
