<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category', 'price', 'description', 'is_active'];

    public function parameters()
    {
        return $this->hasMany(LabParameter::class);
    }
}
