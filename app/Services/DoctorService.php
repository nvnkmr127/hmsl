<?php

namespace App\Services;

use App\Models\Doctor;

class DoctorService
{
    public function getAll()
    {
        return Doctor::with('department')->latest()->get();
    }

    public function create(array $data)
    {
        return Doctor::create($data);
    }

    public function update(Doctor $doctor, array $data)
    {
        $doctor->update($data);
        return $doctor;
    }

    public function toggleActive(Doctor $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);
        return $doctor;
    }

    public function delete(Doctor $doctor)
    {
        return $doctor->delete();
    }
}
