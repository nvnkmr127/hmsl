<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdMedicationChart extends Model
{
    use HasFactory;

    protected $table = 'ipd_medication_charts';

    protected $fillable = [
        'admission_id',
        'patient_id',
        'medicine_id',
        'prescribed_by',
        'doctor_id',
        'medicine_name',
        'dosage',
        'frequency',
        'route',
        'start_date',
        'end_date',
        'instructions',
        'status',
        'stop_reason',
        'stopped_at',
        'stopped_by',
        'is_dispensed',
        'dispensed_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'stopped_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'is_dispensed' => 'boolean',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function stoppedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stopped_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function stop(string $reason, User $user): void
    {
        $this->update([
            'status' => 'Stopped',
            'stop_reason' => $reason,
            'stopped_at' => now(),
            'stopped_by' => $user->id,
        ]);
    }

    public function markDispensed(): void
    {
        $this->update([
            'is_dispensed' => true,
            'dispensed_at' => now(),
        ]);
    }
}
