<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdMedicationAdministration extends Model
{
    use HasFactory;

    protected $fillable = [
        'ipd_medication_chart_id',
        'admission_id',
        'patient_id',
        'administering_nurse_id',
        'administered_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'administered_at' => 'datetime',
    ];

    public function medicationChart(): BelongsTo
    {
        return $this->belongsTo(IpdMedicationChart::class, 'ipd_medication_chart_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administering_nurse_id');
    }
}
