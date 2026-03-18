<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVital extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'weight',
        'height',
        'bmi',
        'temperature',
        'pulse',
        'bp_systolic',
        'bp_diastolic',
        'resp_rate',
        'spo2',
        'blood_sugar',
        'notes',
        'recorded_by',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
