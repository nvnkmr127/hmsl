<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public function generateUHID()
    {
        $prefix = Setting::get('patient_prefix', 'PAT');
        $lastPatient = Patient::latest('id')->first();
        $nextId = $lastPatient ? $lastPatient->id + 1 : 1;
        
        return $prefix . '-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public function getAll($search = null)
    {
        return Patient::query()
            ->when($search, function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('uhid', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['uhid'] = $this->generateUHID();
            $patient = Patient::create($data);
            event(new \App\Events\Patients\PatientRegistered($patient));
            return $patient;
        });
    }

    public function update(Patient $patient, array $data)
    {
        $patient->update($data);
        return $patient;
    }

    public function delete(Patient $patient)
    {
        return $patient->delete();
    }
}
