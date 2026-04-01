<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
