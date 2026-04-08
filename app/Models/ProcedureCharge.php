<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'procedure_id',
        'doctor_id',
        'performed_by',
        'bill_item_id',
        'procedure_name',
        'description',
        'performed_at',
        'charge',
        'quantity',
        'status',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'charge' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function billItem(): BelongsTo
    {
        return $this->belongsTo(BillItem::class);
    }

    public function markAsPerformed(): void
    {
        $this->update([
            'status' => 'Performed',
            'performed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'Cancelled']);
    }
}
