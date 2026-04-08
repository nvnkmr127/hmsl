<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'bill_payment_id',
        'patient_id',
        'admission_id',
        'processed_by',
        'refund_number',
        'amount',
        'reason',
        'notes',
        'status',
        'processed_at',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function billPayment(): BelongsTo
    {
        return $this->belongsTo(BillPayment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'Processed';
    }

    public function approve(User $user): void
    {
        $this->update([
            'status' => 'Approved',
            'approved_by' => $user->id,
        ]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'Rejected']);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'Processed',
            'processed_at' => now(),
        ]);
    }

    public static function generateRefundNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'REF';

        $lastRefund = static::where('refund_number', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRefund) {
            $num = (int) substr($lastRefund->refund_number, -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . $date . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
