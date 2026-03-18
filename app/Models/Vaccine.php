<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    protected $fillable = ['name', 'target_disease', 'recommended_age', 'sequence_order'];

    public function records()
    {
        return $this->hasMany(PatientVaccination::class);
    }
}
