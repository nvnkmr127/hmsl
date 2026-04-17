<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category', 'department_id', 'price', 'description', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
