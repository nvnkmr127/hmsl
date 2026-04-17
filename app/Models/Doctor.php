<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Consultation;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_code',
        'user_id',

        'department_id',
        'full_name',
        'specialization',
        'qualification',
        'phone',
        'email',
        'consultation_fee',
        'registration_number',
        'biography',
        'is_active',
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('doctor_code', 'like', "%{$term}%")
              ->orWhere('specialization', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->when($departmentId, fn($q) => $q->where('department_id', $departmentId));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
