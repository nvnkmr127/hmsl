<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BillingService
{
    public function generateBillNumber()
    {
        $prefix = Setting::get('bill_prefix', 'INV');
        $lastBill = Bill::latest('id')->first();
        $nextId = $lastBill ? $lastBill->id + 1 : 1;
        
        return $prefix . '-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function createBill(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $data['bill_number'] = $this->generateBillNumber();
            $data['created_by'] = Auth::id();
            
            $bill = Bill::create($data);
            
            $subtotal = 0;
            foreach ($items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $bill->items()->create([
                    'item_name' => $item['name'],
                    'item_type' => $item['type'] ?? 'General',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
                $subtotal += $totalPrice;
            }
            
            $totalAmount = $subtotal - ($data['discount_amount'] ?? 0) + ($data['tax_amount'] ?? 0);
            $bill->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount
            ]);
            
            return $bill;
        });
    }

    public function markAsPaid(Bill $bill, $method = 'Cash')
    {
        $bill->update([
            'payment_status' => 'Paid',
            'payment_method' => $method
        ]);
        
        // If it's linked to a consultation, mark consultation as paid too
        if ($bill->consultation_id) {
            $bill->consultation->update(['payment_status' => 'Paid']);
        }

        event(new \App\Events\Billing\BillSettled($bill->load('patient')));
        
        return $bill;
    }
}
