<?php

namespace App\Models;

use App\Sync\Traits\HasSyncMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Consultation;

class Doctor extends Model
{
    use HasFactory, HasSyncMetadata;

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

    /**
     * Normalize doctor name by stripping "Dr." prefix when saving.
     */
    public function setFullNameAttribute($value)
    {
        $normalized = preg_replace('/^(Dr\.?|Doctor)\s+/i', '', trim($value));
        $this->attributes['full_name'] = $normalized;
    }

    /**
     * Always prepend "Dr. " when retrieving.
     */
    public function getFullNameAttribute($value)
    {
        if (empty($value)) return $value;
        $normalized = preg_replace('/^(Dr\.?|Doctor)\s+/i', '', trim($value));
        return 'Dr. ' . $normalized;
    }

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
