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
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'valid_upto' => 'date',
        'fee' => 'decimal:2',
        'weight' => 'decimal:2',
        'temperature' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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


}
