<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getWardNameAttribute()
    {
        return optional(optional($this->bed)->ward)->name ?? 'N/A';
    }
}
