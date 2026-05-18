<?php

namespace App\Models;

use App\Sync\Traits\HasSyncMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, HasSyncMetadata;

    protected $fillable = ['code', 'name', 'description', 'is_active'];


    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}
