<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
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
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'valid_upto' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
