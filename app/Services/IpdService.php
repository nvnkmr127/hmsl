<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Setting;
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
     * Generate a unique admission number.
     */
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

    /**
     * Handle the patient admission process.
     */
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
            
            // Record vitals if provided
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

    /**
     * Handle the patient discharge process.
     */
    public function dischargePatient(Admission $admission, ?string $notes = null): Admission
    {
        return DB::transaction(function () use ($admission, $notes) {
            $admission->update([
                'discharge_date' => now(),
                'status' => 'Discharged',
                'notes' => $notes ?: $admission->notes
            ]);
            
            // Free the bed
            $admission->bed()->update(['is_available' => true]);

            if (Schema::hasColumn('bills', 'admission_id')) {
                $billItems = $this->buildFinalBillItems($admission->fresh());
                $this->billingService->upsertAdmissionFinalBill($admission, $billItems);
            }
            
            return $admission;
        });
    }

    private function buildFinalBillItems(Admission $admission): array
    {
        $admission->loadMissing(['bed.ward', 'labOrders.labTest', 'medications']);

        $items = [];

        $admittedAt = $admission->admission_date ? Carbon::parse($admission->admission_date) : now();
        $dischargedAt = $admission->discharge_date ? Carbon::parse($admission->discharge_date) : now();
        $stayHours = max(0, $admittedAt->diffInHours($dischargedAt));
        $stayDays = max(1, (int) ceil($stayHours / 24));

        $ward = $admission->bed?->ward;
        $dailyCharge = (float) ($ward?->daily_charge ?? 0);

        if ($dailyCharge > 0) {
            $items[] = [
                'name' => ($ward?->name ?? 'Ward') . ' Bed Charges',
                'type' => 'IPD',
                'quantity' => $stayDays,
                'unit_price' => $dailyCharge,
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

        foreach ($admission->medications as $rx) {
            if (!$rx->is_dispensed) {
                continue;
            }

            $meds = is_array($rx->medicines) ? $rx->medicines : [];
            foreach ($meds as $m) {
                $medicineId = isset($m['medicine_id']) ? (int) $m['medicine_id'] : null;
                $name = isset($m['name']) ? trim((string) $m['name']) : '';
                $qty = isset($m['qty']) ? (int) $m['qty'] : 1;
                $qty = max(1, $qty);

                $medicine = null;
                if ($medicineId) {
                    $medicine = \App\Models\Medicine::find($medicineId);
                } elseif ($name !== '') {
                    $medicine = \App\Models\Medicine::query()
                        ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
                        ->first();
                }

                $unitPrice = (float) ($medicine?->selling_price ?? 0);
                if ($unitPrice <= 0) {
                    continue;
                }

                $items[] = [
                    'name' => $medicine?->name ?: ($name ?: 'Medicine'),
                    'type' => 'Pharmacy',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                ];
            }
        }

        return $items;
    }

    public function ensureFinalBill(Admission $admission): void
    {
        if (!Schema::hasColumn('bills', 'admission_id')) {
            return;
        }

        $billItems = $this->buildFinalBillItems($admission->fresh());
        $this->billingService->upsertAdmissionFinalBill($admission, $billItems);
    }

    /**
     * Fetch the detailed admission record for discharge summary/print.
     * Consolidates eager loading to the service layer.
     */
    public function getDischargeDetails(Admission $admission): Admission
    {
        $relations = [
            'patient', 
            'doctor.user', 
            'bed.ward', 
            'vitals', 
            'medications',
            'labOrders.labTest',
        ];

        if (Schema::hasColumn('bills', 'admission_id')) {
            $relations[] = 'finalBill.items';
        }

        return $admission->load($relations);
    }
}
