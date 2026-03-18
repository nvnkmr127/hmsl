<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabParameter extends Model
{
    use HasFactory;

    protected $fillable = ['lab_test_id', 'name', 'unit', 'reference_range'];

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }
}
