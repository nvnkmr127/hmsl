<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVaccination extends Model
{
    protected $fillable = ['patient_id', 'vaccine_id', 'date_given', 'batch_number', 'administered_by', 'notes'];

    protected $casts = [
        'date_given' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }
}
