<?php

namespace App\Services\Webhooks\Factories;

use App\Models\Bill;

class BillingPayloadFactory
{
    public static function forBill(Bill $bill): array
    {
        return [
            'bill_number' => $bill->bill_number,
            'patient_uhid' => $bill->patient?->uhid,
            'total_amount' => (float) $bill->total_amount,
            'paid_amount' => (float) $bill->paid_amount,
            'balance_amount' => (float) $bill->balance_amount,
            'payment_status' => $bill->payment_status,
            'items' => $bill->billItems->map(fn($item) => [
                'name' => $item->item_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])->toArray(),
            'created_at' => $bill->created_at->toISOString(),
        ];
    }
}
