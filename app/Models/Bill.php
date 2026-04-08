<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'patient_id',
        'consultation_id',
        'admission_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_status',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];


    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the total amount in words.
     */
    public function getAmountInWordsAttribute()
    {
        return \Illuminate\Support\Number::spell((float) $this->total_amount);
    }

    public function getPaidAmountAttribute(): float
    {
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments()->get();
        $paid = (float) $payments->where('type', 'payment')->sum('amount');
        $refunded = (float) $payments->where('type', 'refund')->sum('amount');
        return $paid - $refunded;
    }

    public function getBalanceAmountAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }
}
