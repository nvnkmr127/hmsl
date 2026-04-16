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
        'admission_id',
        'order_number',
        'group_uuid',
        'results',
        'status',
        'collected_at',
        'completed_at',
        'technician_id',
        'verified_at',
        'verified_by',
        'notes',
        'bill_item_id',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at'  => 'datetime',
    ];

    public function resultValues()
    {
        return $this->hasMany(LabResultValue::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

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

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }
}
