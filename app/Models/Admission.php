<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_number',
        'patient_id',
        'bed_id',
        'doctor_id',
        'admission_date',
        'discharge_date',
        'reason_for_admission',
        'status',
        'notes',
        'created_by',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'emergency_contact',
        'is_emergency',
    ];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
    ];

    public function vitals()
    {
        return $this->hasMany(PatientVital::class);
    }

    public function medications()
    {
        return $this->hasMany(Prescription::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function ipdNotes()
    {
        return $this->hasMany(IpdNote::class);
    }

    public function doctorNotes()
    {
        return $this->hasMany(IpdNote::class)->where('note_type', 'Doctor');
    }

    public function nurseNotes()
    {
        return $this->hasMany(IpdNote::class)->where('note_type', 'Nurse');
    }

    public function ipdVitals()
    {
        return $this->hasMany(IpdVital::class);
    }

    public function ipdMedications()
    {
        return $this->hasMany(IpdMedicationChart::class);
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(IpdMedicationAdministration::class);
    }

    public function activeMedications()
    {
        return $this->hasMany(IpdMedicationChart::class)->where('status', 'Active');
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function dischargeSummary(): HasOne
    {
        return $this->hasOne(DischargeSummary::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finalBill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    public function getWardNameAttribute()
    {
        return optional(optional($this->bed)->ward)->name ?? 'N/A';
    }

    public function isAdmitted(): bool
    {
        return $this->status === 'Admitted';
    }

    public function isDischarged(): bool
    {
        return $this->status === 'Discharged';
    }

    public function getDaysAdmittedAttribute(): int
    {
        if (!$this->admission_date) {
            return 0;
        }
        $end = $this->discharge_date ?? now();
        return max(1, (int) $this->admission_date->diffInDays($end));
    }
}
