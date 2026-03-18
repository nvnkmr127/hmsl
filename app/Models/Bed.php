<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = ['ward_id', 'bed_number', 'is_available'];

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
}
