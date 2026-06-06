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

    /**
     * Generate an admission number based on the ward type of the selected bed.
     *
     * Ward groups and starting offsets (continuing from previous hospital records):
     *  - NICU          → sequence 'admission_nicu'  (seeded at 3022, so first new = 3023)
     *  - PICU + Rooms  → sequence 'admission_ward'  (seeded at 5119, so first new = 5120)
     */
    public function generateAdmissionNumber(?int $bedId = null, ?int $wardId = null): string
    {
        // Determine sequence group from the ward of the selected bed
        $wardCode = null;
        if ($bedId) {
            $wardCode = \App\Models\Bed::with('ward')
                ->find($bedId)
                ?->ward
                ?->code;
        }

        if (!$wardCode && $wardId) {
            $wardCode = \App\Models\Ward::find($wardId)?->code;
        }

        $isNicu = $wardCode && strtoupper(trim($wardCode)) === 'NICU';

        $sequenceName = $isNicu ? 'admission_nicu' : 'admission_ward';

        // Seed values: the last used number from the previous system.
        // SequenceService will add 1 on first call, so seed = (desired_first - 1).
        $seedValue = $isNicu ? 3022 : 5119;

        $seq = $this->sequenceService->next($sequenceName, null, function () use ($seedValue) {
            // Only called once when the sequence row does not exist yet.
            // Check if there are existing admissions that might have higher numbers.
            return $seedValue;
        });

        $prefix = $isNicu ? 'ADM-NICU-' : 'ADM-';

        return $prefix . $seq;
    }

    public function admitPatient(array $data): Admission
    {
        return DB::transaction(function () use ($data) {
            $data['admission_number'] = $data['admission_number'] ?? $this->generateAdmissionNumber($data['bed_id'] ?? null, $data['ward_id'] ?? null);
            $data['created_by'] = Auth::id();
            $data['status'] = 'Admitted';
            $data['admission_date'] = $data['admission_date'] ?? now()->toDateTimeString();

            // Duplicate admission number check
            if (Admission::where('admission_number', $data['admission_number'])->exists()) {
                throw new \RuntimeException("admission number exist already");
            }

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

            // Sync duplicate beds (if same bed exists in another ward, e.g., AC vs Non-AC)
            $bed->update(['is_available' => false]);
            Bed::where('bed_number', $bed->bed_number)->where('id', '!=', $bed->id)->update(['is_available' => false]);

            $admission = Admission::create($data);

            $bed->loadMissing('ward');
            \App\Models\AdmissionBedHistory::create([
                'admission_id' => $admission->id,
                'bed_id' => $admission->bed_id,
                'start_time' => now(),
                'daily_charge' => $data['daily_charge_override'] ?? $bed->per_day_charge ?? $bed->ward?->daily_charge ?? 0,
            ]);

            $hasVitals = collect(['weight', 'height', 'temperature', 'pulse', 'bp_systolic', 'bp_diastolic', 'resp_rate', 'respiratory_rate', 'spo2'])
                ->contains(fn($key) => !empty($data[$key]));

            if ($hasVitals) {
                $respRate = $data['resp_rate'] ?? $data['respiratory_rate'] ?? null;
                $vitalData = [
                    'patient_id' => $admission->patient_id,
                    'admission_id' => $admission->id,
                    'weight' => $data['weight'] ?? null,
                    'height' => $data['height'] ?? null,
                    'temperature' => $data['temperature'] ?? null,
                    'pulse' => $data['pulse'] ?? null,
                    'bp_systolic' => $data['bp_systolic'] ?? null,
                    'bp_diastolic' => $data['bp_diastolic'] ?? null,
                    'resp_rate' => $respRate,
                    'spo2' => $data['spo2'] ?? null,
                    'recorded_by' => Auth::id(),
                ];

                \App\Models\PatientVital::create($vitalData);

                // Also create an IPD specific vital record for the chart
                $vitalData['recorded_at'] = now();
                \App\Models\IpdVital::create($vitalData);
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
            // We skip auto-recalculate here to preserve manual bill adjustments made in the Discharge Process popup.
            // if (Schema::hasColumn('bills', 'admission_id')) { ... }


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

            // Close old history
            $activeHistory = \App\Models\AdmissionBedHistory::where('admission_id', $admission->id)
                ->whereNull('end_time')
                ->latest('start_time')
                ->first();

            if ($activeHistory) {
                $activeHistory->update(['end_time' => now()]);
            }

            // 7. BED RELEASE
            $admission->loadMissing('bed');
            if ($admission->bed) {
                $admission->bed->update(['is_available' => true]);
                Bed::where('bed_number', $admission->bed->bed_number)->where('id', '!=', $admission->bed->id)->update(['is_available' => true]);
            }

            event(new \App\Events\IPD\PatientDischarged($admission->load(['patient', 'bed.ward', 'doctor.user'])));

            return $admission;
        });
    }

    public function transferPatient(Admission $admission, int $newBedId, ?string $notes = null, ?float $dailyChargeOverride = null): Admission
    {
        return DB::transaction(function () use ($admission, $newBedId, $notes, $dailyChargeOverride) {
            $admission = Admission::query()->lockForUpdate()->find($admission->id);
            if ($admission->status !== 'Admitted') {
                throw new \RuntimeException('Only admitted patients can be transferred.');
            }

            if ($admission->bed_id == $newBedId) {
                throw new \RuntimeException('Patient is already in the selected bed.');
            }

            $newBed = Bed::query()->lockForUpdate()->findOrFail($newBedId);
            if (!$newBed->is_available) {
                throw new \RuntimeException('Selected bed is no longer available.');
            }

            // Close old history
            $activeHistory = \App\Models\AdmissionBedHistory::where('admission_id', $admission->id)
                ->whereNull('end_time')
                ->latest('start_time')
                ->first();

            if ($activeHistory) {
                $activeHistory->update(['end_time' => now()]);
            }

            // End current bed history and free bed(s)
            $admission->loadMissing('bed');
            if ($admission->bed) {
                $admission->bed->update(['is_available' => true]);
                Bed::where('bed_number', $admission->bed->bed_number)->where('id', '!=', $admission->bed->id)->update(['is_available' => true]);
            }

            // Occupy new bed
            $newBed->update(['is_available' => false]);
            Bed::where('bed_number', $newBed->bed_number)->where('id', '!=', $newBed->id)->update(['is_available' => false]);
            
            // Update admission
            $admission->update(['bed_id' => $newBedId]);

            // Start new history
            $newBed->loadMissing('ward');
            \App\Models\AdmissionBedHistory::create([
                'admission_id' => $admission->id,
                'bed_id' => $newBedId,
                'start_time' => now(),
                'daily_charge' => $dailyChargeOverride ?? $newBed->per_day_charge ?? $newBed->ward?->daily_charge ?? 0,
            ]);

            return $admission;
        });
    }

    public function buildFinalBillItems(Admission $admission): array
    {
        $admission->load(['bed.ward', 'bed', 'labOrders.labTest', 'ipdMedications.medicine']);

        $items = [];

        $bedHistories = \App\Models\AdmissionBedHistory::with(['bed.ward'])->where('admission_id', $admission->id)->get();

        if ($bedHistories->isEmpty()) {
            $admittedAt = $admission->admission_date ? Carbon::parse($admission->admission_date) : now();
            $dischargedAt = $admission->discharge_date ? Carbon::parse($admission->discharge_date) : now();
            $stayHours = max(0, $admittedAt->diffInHours($dischargedAt));
            $stayDays = max(1, (int) ceil($stayHours / 24));

            $ward = $admission->bed?->ward;
            $bed = $admission->bed;
            $dailyCharge = (float) ($bed?->per_day_charge ?? $ward?->daily_charge ?? 0);

            if ($dailyCharge > 0) {
                $items[] = [
                    'name' => ($ward?->name ?? 'Ward') . ' - ' . ($bed?->bed_number ?? 'Bed') . ' [' . $admittedAt->format('d/m') . ' - ' . $dischargedAt->format('d/m') . ']',
                    'type' => 'IPD',
                    'quantity' => $stayDays,
                    'unit_price' => $dailyCharge,
                ];
            }
        } else {
            foreach ($bedHistories as $history) {
                $start = Carbon::parse($history->start_time);
                $end = $history->end_time ? Carbon::parse($history->end_time) : now();
                
                $stayHours = max(0, $start->diffInHours($end));
                $stayDays = max(1, (int) ceil($stayHours / 24));
                
                $ward = $history->bed?->ward;
                $bed = $history->bed;
                $dailyCharge = (float) ($history->daily_charge ?? $bed?->per_day_charge ?? $ward?->daily_charge ?? 0);

                if ($dailyCharge > 0) {
                    $items[] = [
                        'name' => ($ward?->name ?? 'Ward') . ' - ' . ($bed?->bed_number ?? 'Bed') . ' [' . $start->format('d/m') . ' - ' . $end->format('d/m') . ']',
                        'type' => 'IPD',
                        'quantity' => $stayDays,
                        'unit_price' => $dailyCharge,
                    ];
                }
            }
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
                    'name' => 'Doctor Visit Charges - ' . $doctor->full_name . ' (' . $doctorVisitDays . ' days)',
                    'type' => 'Consultation',
                    'quantity' => $doctorVisitDays,
                    'unit_price' => $visitCharge,
                ];
            }
        }

        $labOrders = \App\Models\LabOrder::with('labTest')
            ->where('admission_id', $admission->id)
            ->get();

        foreach ($labOrders as $order) {
            $price = (float) ($order->labTest?->price ?? 0);
            
            $items[] = [
                'name' => ($order->labTest?->name ?? 'Lab Test') . ' (#' . $order->order_number . ')',
                'type' => 'Lab',
                'quantity' => 1,
                'unit_price' => $price,
                'source_type' => \App\Models\LabOrder::class,
                'source_id' => $order->id,
            ];
        }

        $medications = \App\Models\IpdMedicationChart::with('medicine')
            ->where('admission_id', $admission->id)
            ->get();

        foreach ($medications as $rx) {
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
                    'medicine_id' => $rx->medicine_id,
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
        // Removed auto sync to preserve manual adjustments
        // if ($admission->finalBill()->exists()) {
        //     $this->ensureFinalBill($admission);
        // }

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
