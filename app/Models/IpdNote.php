<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'note_type',
        'note_date',
        'content',
        'is_editable',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'note_date' => 'datetime',
        'locked_at' => 'datetime',
        'is_editable' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        if (!$this->is_editable || $this->is_locked) {
            return false;
        }

        if ($this->note_date->diffInHours(now()) > 24) {
            return false;
        }

        return true;
    }

    public function lock(): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);
    }
}
