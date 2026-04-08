<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DischargeMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'discharge_summary_id',
        'medicine_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration',
        'route',
        'instructions',
        'quantity',
        'unit_price',
        'is_continued',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'is_continued' => 'boolean',
    ];

    public function dischargeSummary(): BelongsTo
    {
        return $this->belongsTo(DischargeSummary::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
