<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'quantity_change',
        'type',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

