<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DischargeSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'admission_number',
        'uhid',
        'admission_date',
        'discharge_date',
        'admission_diagnosis',
        'final_diagnosis',
        'treatment_summary',
        'procedures_done',
        'investigations_summary',
        'condition_at_discharge',
        'condition_notes',
        'general_advice',
        'diet_advice',
        'activity_advice',
        'follow_up_date',
        'follow_up_notes',
        'status',
        'is_finalized',
        'finalized_at',
        'finalized_by',
    ];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
        'follow_up_date' => 'date',
        'finalized_at' => 'datetime',
        'is_finalized' => 'boolean',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(DischargeMedication::class);
    }

    public function isEditable(): bool
    {
        return !$this->is_finalized && $this->status !== 'Finalized';
    }

    public function canFinalize(): bool
    {
        if ($this->is_finalized || $this->status === 'Finalized') {
            return false;
        }

        // MANDATORY FIELDS VALIDATION
        return !empty($this->final_diagnosis) && 
               !empty($this->treatment_summary) && 
               !empty($this->condition_at_discharge);
    }

    public function markAsFinalized(User $user): void
    {
        $this->update([
            'status' => 'Finalized',
            'is_finalized' => true,
            'finalized_at' => now(),
            'finalized_by' => $user->id,
        ]);
    }
}
