<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'admission_id',
        'consultation_id',
        'doctor_id',
        'created_by',
        'diagnosis_name',
        'icd_code',
        'type',
        'status',
        'notes',
        'diagnosed_date',
        'resolved_date',
    ];

    protected $casts = [
        'diagnosed_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPrimary(): bool
    {
        return $this->type === 'Primary';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'Confirmed';
    }
}
