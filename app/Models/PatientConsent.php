<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'signed_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'signed_at' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

