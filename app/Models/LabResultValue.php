<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResultValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'patient_id',
        'lab_parameter_id',
        'result_value',
        'technician_id',
        'verified_by',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function parameter()
    {
        return $this->belongsTo(LabParameter::class, 'lab_parameter_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
