<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'department_id',
        'slot_id',
        'created_by',
        'appointment_date',
        'appointment_time',
        'token_number',
        'type',
        'status',
        'reason',
        'notes',
        'checked_in_at',
        'completed_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'checked_in_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isScheduled(): bool
    {
        return $this->status === 'Scheduled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    public function canCheckIn(): bool
    {
        return $this->status === 'Scheduled';
    }

    public function checkIn(): void
    {
        $this->update([
            'status' => 'Checked-in',
            'checked_in_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'Completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'Cancelled']);
    }

    public function markNoShow(): void
    {
        $this->update(['status' => 'No-show']);
    }

    public static function generateTokenNumber(string $prefix = 'APT'): string
    {
        $date = now()->format('Ymd');
        $lastToken = static::whereDate('appointment_date', now()->toDateString())
            ->where('token_number', 'like', $prefix . $date . '%')
            ->max('token_number');

        if ($lastToken) {
            $num = (int) substr($lastToken, -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . $date . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
