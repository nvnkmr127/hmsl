<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'bill_item_id',
        'doctor_id',
        'applied_by',
        'approved_by',
        'discount_type',
        'discount_value',
        'applied_amount',
        'reason',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
