<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BillingService
{
    protected SequenceService $sequenceService;

    public function __construct(SequenceService $sequenceService)
    {
        $this->sequenceService = $sequenceService;
    }

    public function generateBillNumber()
    {
        $prefix = Setting::get('bill_prefix', 'INV');
        $scope = date('Ymd');

        $seq = $this->sequenceService->next('bill', $scope, function () use ($scope) {
            $max = 0;
            $existing = Bill::query()
                ->where('bill_number', 'like', '%-' . $scope . '-%')
                ->get(['bill_number']);

            foreach ($existing as $bill) {
                $parts = explode('-', (string) $bill->bill_number);
                $n = (int) end($parts);
                $max = max($max, $n);
            }

            return $max;
        });

        return $prefix . '-' . $scope . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function createBill(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $data['bill_number'] = $this->generateBillNumber();
            $data['created_by'] = Auth::id();
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['payment_status'] = $data['payment_status'] ?? 'Unpaid';
            
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

            $this->applyInitialPayment($bill, $data);
            $this->recalculatePaymentStatus($bill);
            
            return $bill;
        });
    }

    public function upsertAdmissionFinalBill(Admission $admission, array $items): Bill
    {
        if (!Schema::hasColumn('bills', 'admission_id')) {
            throw new \RuntimeException("Missing database column 'bills.admission_id'. Run migrations first.");
        }

        return DB::transaction(function () use ($admission, $items) {
            $bill = Bill::firstOrNew(['admission_id' => $admission->id]);

            if (!$bill->exists) {
                $bill->bill_number = $this->generateBillNumber();
                $bill->payment_status = 'Unpaid';
            }

            $bill->patient_id = $admission->patient_id;
            $bill->consultation_id = null;
            $bill->created_by = Auth::id();
            $bill->notes = 'Final discharge bill for admission ' . $admission->admission_number;
            $bill->discount_amount = $bill->discount_amount ?? 0;
            $bill->tax_amount = $bill->tax_amount ?? 0;
            $bill->save();

            $bill->items()->delete();

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

            $totalAmount = $subtotal - ($bill->discount_amount ?? 0) + ($bill->tax_amount ?? 0);
            $bill->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            $this->recalculatePaymentStatus($bill);

            return $bill->load(['patient', 'items', 'creator']);
        });
    }

    public function markAsPaid(Bill $bill, $method = 'Cash')
    {
        return DB::transaction(function () use ($bill, $method) {
            $bill = Bill::query()->lockForUpdate()->findOrFail($bill->id);
            $balance = (float) $bill->balance_amount;
            if ($balance <= 0) {
                return $bill;
            }

            $this->recordPayment($bill, $balance, (string) $method, 'payment', null, null);
            $this->recalculatePaymentStatus($bill);

            return $bill->fresh();
        });
    }

    public function recordPayment(
        Bill $bill,
        float $amount,
        ?string $method = null,
        string $type = 'payment',
        ?string $reference = null,
        ?string $notes = null
    ): BillPayment {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than 0.');
        }

        $payment = BillPayment::create([
            'bill_id' => $bill->id,
            'amount' => $amount,
            'type' => $type,
            'method' => $method,
            'reference' => $reference,
            'notes' => $notes,
            'received_by' => Auth::id(),
            'received_at' => now(),
        ]);

        event(new \App\Events\Billing\PaymentReceived($payment->load(['bill.patient'])));

        return $payment;
    }

    private function applyInitialPayment(Bill $bill, array $data): void
    {
        $status = (string) ($data['payment_status'] ?? 'Unpaid');
        $method = $data['payment_method'] ?? null;

        if ($status === 'Paid') {
            $this->recordPayment($bill, (float) $bill->total_amount, $method, 'payment', null, $data['notes'] ?? null);
            return;
        }

        if (in_array($status, ['Partial', 'Partially Paid'], true)) {
            $paidAmount = isset($data['paid_amount']) ? (float) $data['paid_amount'] : 0;
            if ($paidAmount > 0) {
                $this->recordPayment($bill, $paidAmount, $method, 'payment', null, $data['notes'] ?? null);
            }
        }
    }

    public function recalculatePaymentStatus(Bill $bill): void
    {
        $bill->load('payments');

        $paid = (float) $bill->paid_amount;
        $total = (float) $bill->total_amount;
        $balance = $total - $paid;

        $oldStatus = (string) $bill->payment_status;

        $newStatus = 'Unpaid';
        if ($total <= 0) {
            $newStatus = 'Paid';
        } elseif ($paid <= 0) {
            $newStatus = 'Unpaid';
        } elseif ($balance > 0) {
            $newStatus = 'Partially Paid';
        } else {
            $newStatus = 'Paid';
        }

        $latestMethod = $bill->payments()
            ->whereNotNull('method')
            ->latest('received_at')
            ->value('method');

        $bill->update([
            'payment_status' => $newStatus,
            'payment_method' => $latestMethod ?: $bill->payment_method,
        ]);

        if ($newStatus === 'Paid' && $oldStatus !== 'Paid') {
            if ($bill->consultation_id) {
                $bill->consultation()->update(['payment_status' => 'Paid']);
            }

            event(new \App\Events\Billing\BillSettled($bill->fresh()->load('patient')));
        }
    }
}
