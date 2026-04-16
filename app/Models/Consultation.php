<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'service_id',
        'doctor_id',
        'weight',
        'temperature',
        'token_number',
        'consultation_date',
        'valid_upto',
        'fee',
        'status',
        'payment_status',
        'payment_method',
        'notes',
        'discount_amount',
        'chief_complaints',
        'history_of_present_illness',
        'past_history',
        'personal_history',
        'general_examination',
        'systemic_examination',
        'examination_findings',
        'diagnosis_notes',
        'advice',
        'follow_up_date',
        'created_by',
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'valid_upto' => 'date',
        'follow_up_date' => 'date',
        'fee' => 'decimal:2',
        'weight' => 'decimal:2',
        'temperature' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'chief_complaints' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function bill()
    {
        return $this->hasOne(Bill::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function vitals()
    {
        return $this->hasMany(PatientVital::class);
    }
}
