<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'type', 'daily_charge', 'capacity', 'is_active'];


    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
