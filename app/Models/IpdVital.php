<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdVital extends Model
{
    use HasFactory;

    protected $table = 'ipd_vitals';

    protected $fillable = [
        'admission_id',
        'patient_id',
        'recorded_by',
        'recorded_at',
        'bp_systolic',
        'bp_diastolic',
        'bp',
        'pulse',
        'temperature',
        'spo2',
        'resp_rate',
        'weight',
        'height',
        'bmi',
        'pain_scale',
        'glasgow_coma_scale',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'bp_systolic' => 'decimal:2',
        'bp_diastolic' => 'decimal:2',
        'pulse' => 'decimal:2',
        'temperature' => 'decimal:1',
        'spo2' => 'decimal:1',
        'resp_rate' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:1',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getBpAttribute(): ?string
    {
        if ($this->bp_systolic && $this->bp_diastolic) {
            return $this->bp_systolic . '/' . $this->bp_diastolic;
        }
        return $this->bp;
    }

    public function isAbnormalBp(): bool
    {
        if (!$this->bp_systolic || !$this->bp_diastolic) {
            return false;
        }
        return $this->bp_systolic > 140 || $this->bp_diastolic > 90 || $this->bp_systolic < 90 || $this->bp_diastolic < 60;
    }

    public function isAbnormalTemperature(): bool
    {
        if (!$this->temperature) {
            return false;
        }
        return $this->temperature > 99.5 || $this->temperature < 97.0;
    }

    public function isAbnormalSpo2(): bool
    {
        if (!$this->spo2) {
            return false;
        }
        return $this->spo2 < 95;
    }
}
