<?php

namespace App\Services\Webhooks\Factories;

use App\Models\LabOrder;

class LabPayloadFactory
{
    public static function forOrder(LabOrder $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'patient' => [
                'id' => $order->patient_id,
                'name' => $order->patient?->full_name,
                'uhid' => $order->patient?->uhid,
            ],
            'test' => [
                'id' => $order->lab_test_id,
                'name' => $order->labTest?->name,
                'code' => $order->labTest?->test_code,
            ],
            'priority' => $order->priority,
            'status' => $order->status,
            'results' => $order->results,
            'verified_by' => [
                'id' => $order->verified_by,
                'name' => $order->verifiedBy?->name,
            ],
            'created_at' => $order->created_at?->toIso8601String(),
            'completed_at' => $order->completed_at?->toIso8601String(),
        ];
    }
}
