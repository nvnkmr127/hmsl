<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'admission_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'chief_complaint',
        'diagnosis',
        'advice',
        'follow_up_date',
        'medicines',
        'is_dispensed',
        'dispensed_at',
        'dispensed_by',
    ];

    protected $casts = [
        'medicines'       => 'array',
        'follow_up_date'  => 'date',
        'is_dispensed'    => 'boolean',
        'dispensed_at'    => 'datetime',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }
}
