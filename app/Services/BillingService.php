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
            
            // FIX: Recalculate tax based on a standard rate if not provided, or maintain if fixed
            $taxRate = (float) Setting::get('tax_rate', 0); // Assuming percentage 0-100
            
            $bill->save();

            // NON-DESTRUCTIVE UPDATE: Map existing items to avoid sweeping deletion
            $existingItems = $bill->items()->get()->keyBy('item_name');
            $newItems = collect($items);
            $processedItemIds = [];

            $subtotal = 0;
            foreach ($items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                
                $billItem = $bill->items()->updateOrCreate(
                    ['item_name' => $item['name']],
                    [
                        'item_type' => $item['type'] ?? 'General',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $totalPrice,
                    ]
                );
                
                $processedItemIds[] = $billItem->id;
                $subtotal += $totalPrice;

                // LINK SOURCE RECORD
                if (isset($item['source_type']) && isset($item['source_id'])) {
                    $item['source_type']::whereKey($item['source_id'])->update(['bill_item_id' => $billItem->id]);
                }

                // INVENTORY SYNC: Reduce stock for pharmacy items
                if (strtolower($item['type'] ?? '') === 'pharmacy' && $billItem->wasRecentlyCreated) {
                    $this->syncInventoryForItem($item);
                }
            }

            // CLEANUP: Remove old items that are no longer in the new set
            $bill->items()->whereNotIn('id', $processedItemIds)->delete();

            $taxAmount = $taxRate > 0 ? ($subtotal * ($taxRate / 100)) : ($bill->tax_amount ?? 0);
            $totalAmount = $subtotal - ($bill->discount_amount ?? 0) + $taxAmount;

            $oldTotal = (float) $bill->total_amount;
            $bill->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            if ($oldTotal != (float) $totalAmount) {
                \Illuminate\Support\Facades\Log::channel('heavy')->info('BILL_TOTAL_UPDATED', [
                    'bill_id' => $bill->id,
                    'old_total' => $oldTotal,
                    'new_total' => (float) $totalAmount,
                    'user_id' => Auth::id(),
                    'reason' => 'IPD recalculation'
                ]);
            }

            $this->recalculatePaymentStatus($bill);

            return $bill->load(['patient', 'items', 'creator']);
        });
    }

    protected function syncInventoryForItem(array $item): void
    {
        // Try to find the medicine by name or a reference if provided
        $name = $item['name'];
        // Note: In a production system, we'd use a medicine_id here. 
        // For now, we attempt to resolve it from the name or common patterns.
        $medicine = \App\Models\Medicine::where('name', $name)
            ->orWhereRaw('CONCAT(name, " - ", strength) = ?', [$name])
            ->first();

        if ($medicine) {
            app(MedicineService::class)->adjustStock(
                $medicine,
                -1 * $item['quantity'],
                'dispense',
                Bill::class,
                null,
                'Auto-deducted via billing'
            );
        }
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

        // AUTHORIZATION: Only admins can process refunds
        if ($type === 'refund' && !Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            throw new \RuntimeException('Unauthorized: Only administrators or managers can process refunds.');
        }

        // PREVENT OVERPAYER: Check balance before recording
        if ($type === 'payment') {
            $balance = (float) $bill->balance_amount;
            if ($amount > ($balance + 0.01)) { // 0.01 tolerance for floating point
                 throw new \InvalidArgumentException("Payment amount ({$amount}) exceeds the remaining balance ({$balance}).");
            }
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
        $bill->load(['payments', 'discounts']);

        $totalDiscounts = (float) $bill->discounts()->where('status', 'approved')->sum('applied_amount');
        $paid = (float) $bill->paid_amount;
        $subtotal = (float) $bill->subtotal;
        $tax = (float) $bill->tax_amount;
        
        $total = $subtotal - $totalDiscounts + $tax;
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
            'discount_amount' => $totalDiscounts,
            'total_amount' => $total,
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

    public function applyDiscount(Bill $bill, array $discountData): \App\Models\BillDiscount
    {
        return DB::transaction(function () use ($bill, $discountData) {
            // 1. PREVENT DISCOUNT AFTER PAYMENT (If fully paid)
            if ($bill->payment_status === 'Paid' && $bill->total_amount > 0) {
                throw new \InvalidArgumentException('Cannot apply discount to a fully paid bill.');
            }

            // 2. AUTHORIZATION & CONTEXT
            $user = Auth::user();
            $userDoctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);
            $isSoleDoctor = \App\Models\Doctor::active()->count() === 1;

            // 3. Clinical Authorization Check
            $clinicalDoctorId = null;
            $clinicalLimit = 0;
            $isClinicallyAuthorized = false;

            if ($bill->consultation_id) {
                $consultation = $bill->consultation;
                $clinicalDoctorId = $consultation->doctor_id;
                $isClinicallyAuthorized = $consultation->is_discount_authorized;
                $clinicalLimit = (float) $consultation->authorized_discount_limit;
            } elseif ($bill->admission_id) {
                $admission = $bill->admission;
                $clinicalDoctorId = $admission->doctor_id;
                $isClinicallyAuthorized = $admission->is_discount_authorized;
                $clinicalLimit = (float) $admission->authorized_discount_limit;
            }

            // 4. LIMITS & SETTINGS
            $requireDocApproval = filter_var(Setting::get('require_doctor_approval_for_discounts', false), FILTER_VALIDATE_BOOLEAN);
            $maxPct = (float) Setting::get('max_discount_percentage', 20);
            $maxAmt = (float) Setting::get('max_discount_amount', 5000);
            $approvalThreshold = (float) Setting::get('discount_approval_threshold', 15);

            $type = $discountData['type']; // percentage or flat
            $value = (float) $discountData['value'];
            $reason = $discountData['reason'];
            $itemId = $discountData['bill_item_id'] ?? null;

            if (empty($reason)) {
                throw new \InvalidArgumentException('Reason is mandatory for applying a discount.');
            }

            $subtotal = $itemId 
                ? $bill->items()->findOrFail($itemId)->total_price 
                : $bill->subtotal;

            $calculatedAmount = 0;
            if ($type === 'percentage') {
                if ($value > 100) throw new \InvalidArgumentException('Percentage cannot exceed 100%.');
                $calculatedAmount = ($subtotal * $value) / 100;
            } else {
                $calculatedAmount = $value;
            }

            if ($calculatedAmount > $subtotal) {
                throw new \InvalidArgumentException('Discount cannot exceed the bill amount.');
            }

            // 5. APPROVAL & AUDIT LOGIC
            // Default: approved for doctors and admins
            $status = 'approved';
            $linkingDoctorId = $userDoctor?->id ?: $clinicalDoctorId;

            if ($userDoctor || $isAdmin) {
                // Doctors and Admins are auto-approved
                $status = 'approved';
                
                // If it's a doctor but not the clinical doctor, we still record them as the doctor_id for this discount
                if ($userDoctor) {
                    $linkingDoctorId = $userDoctor->id;
                }
            } else {
                // STAFF applying
                if ($requireDocApproval) {
                    // Check if it's within clinical authorization (one approval required)
                    if ($isClinicallyAuthorized && $calculatedAmount <= $clinicalLimit) {
                        $status = 'approved';
                    } else {
                        // Needs approval if no pre-authorization or exceeds it
                        $status = 'pending';
                    }
                } else {
                    // Staff can apply directly if approval not required by setting
                    $status = 'approved';
                }
            }

            // Global threshold check for non-admins (even doctors have a ceiling for auto-approval if configured)
            if (!$isAdmin && !$isSoleDoctor && $status === 'approved') {
                $pct = ($calculatedAmount / $subtotal) * 100;
                if ($pct > $maxPct || $calculatedAmount > $maxAmt) {
                     throw new \InvalidArgumentException("Discount exceeds the hospital's maximum allowed limit ({$maxPct}% or ₹{$maxAmt}).");
                }
                
                if ($pct > $approvalThreshold) {
                    $status = 'pending';
                }
            }

            $discount = \App\Models\BillDiscount::create([
                'bill_id' => $bill->id,
                'bill_item_id' => $itemId,
                'doctor_id' => $linkingDoctorId,
                'applied_by' => $user->id,
                'approved_by' => $status === 'approved' ? $user->id : null,
                'discount_type' => $type,
                'discount_value' => $value,
                'applied_amount' => $calculatedAmount,
                'reason' => $reason,
                'status' => $status,
                'applied_at' => now(),
            ]);

            if ($status === 'approved') {
                $this->recalculatePaymentStatus($bill);
            }

            return $discount;
        });
    }

    public function approveDiscount(\App\Models\BillDiscount $discount): void
    {
        if ($discount->status !== 'pending') return;

        DB::transaction(function () use ($discount) {
            $discount->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'applied_at' => now(), // Refresh since it's now officially active
            ]);

            $this->recalculatePaymentStatus($discount->bill);
        });
    }

    public function rejectDiscount(\App\Models\BillDiscount $discount): void
    {
        if ($discount->status !== 'pending') return;

        $discount->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
        ]);
        
        // No need to recalculate payment status as it was never 'approved'
    }
}
