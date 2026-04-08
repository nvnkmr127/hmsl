<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class HospitalOwner extends Model
{
    protected $fillable = ['singleton', 'doctor_id'];

    protected $casts = [
        'singleton' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    private static function tableReady(): bool
    {
        return Schema::hasTable('hospital_owners');
    }

    public static function current(): ?self
    {
        if (!self::tableReady()) {
            return null;
        }

        return self::with('doctor.user')->where('singleton', 1)->first();
    }

    public static function ownerDoctor(): ?Doctor
    {
        return self::current()?->doctor;
    }

    public static function ownerUser(): ?User
    {
        return self::ownerDoctor()?->user;
    }

    public static function isOwner(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $ownerUser = self::ownerUser();
        if (!$ownerUser) {
            return false;
        }

        return (int) $ownerUser->id === (int) $user->id;
    }

    public static function setOwnerDoctor(Doctor $doctor): self
    {
        if (!self::tableReady()) {
            throw new \RuntimeException("Missing database table 'hospital_owners'. Run migrations first.");
        }

        return self::updateOrCreate(
            ['singleton' => 1],
            ['doctor_id' => $doctor->id]
        );
    }
}
