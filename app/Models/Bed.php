<?php

namespace App\Models;

use App\Sync\Traits\HasSyncMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory, HasSyncMetadata;

    protected $fillable = ['ward_id', 'bed_number', 'is_available'];

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
}
