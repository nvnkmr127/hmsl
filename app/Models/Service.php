<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category', 'department_id', 'price', 'description', 'is_active'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

}
