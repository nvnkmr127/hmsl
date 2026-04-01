<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'lab_test_id',
        'consultation_id',
        'results',
        'status',
        'collected_at',
        'completed_at',
        'technician_id',
        'notes',
    ];

    protected $casts = [
        'results'      => 'array',
        'collected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
