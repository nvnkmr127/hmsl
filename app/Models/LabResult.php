<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'patient_id',
        'result_values',
        'result_notes',
        'interpretations',
        'status',
        'is_abnormal',
        'is_critical',
        'resulted_at',
        'resulted_by',
    ];

    protected $casts = [
        'result_values' => 'array',
        'is_abnormal' => 'boolean',
        'is_critical' => 'boolean',
        'resulted_at' => 'datetime',
    ];

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function resultedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resulted_by');
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'Completed',
            'resulted_at' => now(),
        ]);
    }

    public function hasAbnormalValues(): bool
    {
        return $this->is_abnormal;
    }

    public function isCritical(): bool
    {
        return $this->is_critical;
    }
}
