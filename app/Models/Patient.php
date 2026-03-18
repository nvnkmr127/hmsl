<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'uhid',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'blood_group',
        'address',
        'city',
        'state',
        'pincode',
        'emergency_contact_name',
        'emergency_contact_phone',
        'marital_status',
        'is_active',
        'allergies',
        'insurance_provider',
        'insurance_policy',
        'insurance_validity',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) return '--';
        $dob = Carbon::parse($this->date_of_birth);
        $now = Carbon::now();
        
        $years = (int)$dob->diffInYears($now);
        $months = (int)$dob->diffInMonths($now) % 12;
        $days = (int)$dob->diffInDays($now->copy()->subMonths($months)->subYears($years));

        if ($years >= 5) {
            return $years . 'y';
        }

        if ($years >= 1) {
            return "{$years}y {$months}m";
        }

        if ($months >= 1) {
            return "{$months}m {$days}d";
        }

        return "{$days} days";
    }

    public function vitals()
    {
        return $this->hasMany(PatientVital::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(PatientVaccination::class);
    }
}
