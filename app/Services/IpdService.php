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
            $admission->update([
                'discharge_date' => now(),
                'status' => 'Discharged',
                'notes' => $notes ?: $admission->notes
            ]);

            $admission->bed()->update(['is_available' => true]);

            if (Schema::hasColumn('bills', 'admission_id')) {
                $billItems = $this->buildFinalBillItems($admission->fresh());
                $this->billingService->upsertAdmissionFinalBill($admission, $billItems);
            }

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
            $doctorVisitDays = max(1, $stayDays);
            $items[] = [
                'name' => 'Doctor Visit Charges - Dr. ' . $doctor->full_name . ' (' . $doctorVisitDays . ' days)',
                'type' => 'Consultation',
                'quantity' => $doctorVisitDays,
                'unit_price' => $visitCharge,
            ];
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
            ];
        }

        foreach ($admission->ipdMedications as $rx) {
            if ($rx->status !== 'Active' && $rx->status !== 'Stopped') {
                continue;
            }

            $medicine = $rx->medicine;
            if (!$medicine) {
                continue;
            }

            $unitPrice = (float) ($medicine->selling_price ?? $medicine->price ?? 0);
            if ($unitPrice <= 0) {
                continue;
            }

            $qty = $this->calculateMedicationQuantity($rx);

            $items[] = [
                'name' => $rx->medicine_name . ($rx->dosage ? ' - ' . $rx->dosage : ''),
                'type' => 'Pharmacy',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
            ];
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
