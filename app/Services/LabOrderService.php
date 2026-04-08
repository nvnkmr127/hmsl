<?php

namespace App\Services;

use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabOrderService
{
    protected SequenceService $sequenceService;

    public function __construct(SequenceService $sequenceService)
    {
        $this->sequenceService = $sequenceService;
    }

    public function createOrders(array $data, array $labTestIds): array
    {
        return DB::transaction(function () use ($data, $labTestIds) {
            $prefix = Setting::get('lab_order_prefix', 'LAB');
            $scope = now()->format('Ymd');

            $seq = $this->sequenceService->next('lab_order', $scope, function () use ($scope) {
                $max = 0;
                $existing = LabOrder::query()
                    ->whereNotNull('order_number')
                    ->where('order_number', 'like', '%-' . $scope . '-%')
                    ->get(['order_number']);

                foreach ($existing as $order) {
                    $parts = explode('-', (string) $order->order_number);
                    $n = (int) end($parts);
                    $max = max($max, $n);
                }

                return $max;
            });

            $orderNumber = $prefix . '-' . $scope . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $groupUuid = (string) Str::uuid();

            $patientId = (int) $data['patient_id'];
            $doctorId = isset($data['doctor_id']) ? (int) $data['doctor_id'] : null;
            $consultationId = isset($data['consultation_id']) ? (int) $data['consultation_id'] : null;
            $admissionId = isset($data['admission_id']) ? (int) $data['admission_id'] : null;
            $notes = $data['notes'] ?? null;

            $created = [];
            foreach ($labTestIds as $testId) {
                $testId = (int) $testId;
                if ($testId <= 0) {
                    continue;
                }

                $test = LabTest::find($testId);
                if (!$test) {
                    continue;
                }

                $order = LabOrder::create([
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'consultation_id' => $consultationId,
                    'admission_id' => $admissionId,
                    'lab_test_id' => $testId,
                    'order_number' => $orderNumber,
                    'group_uuid' => $groupUuid,
                    'status' => 'Pending',
                    'notes' => $notes,
                ]);

                event(new \App\Events\Laboratory\LabOrderCreated($order->load(['patient', 'doctor', 'labTest'])));
                $created[] = $order;
            }

            return $created;
        });
    }
}

