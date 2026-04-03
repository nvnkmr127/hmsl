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

    public function getAll(?string $search = null, array $filters = [], string $sortBy = 'latest')
    {
        return Patient::query()
            ->when($search, fn($q) => $q->search($search))
            ->when($filters['gender'] ?? null, fn($q) => $q->where('gender', $filters['gender']))
            ->when($filters['blood_group'] ?? null, fn($q) => $q->where('blood_group', $filters['blood_group']))
            ->when($sortBy === 'alphabetic', fn($q) => $q->orderBy('first_name'))
            ->latest()
            ->paginate(10);
    }

    public function getStats()
    {
        return [
            'total' => Patient::count(),
            'today' => Patient::whereDate('created_at', now())->count(),
            'male'  => Patient::where('gender', 'Male')->count(),
            'female'=> Patient::where('gender', 'Female')->count(),
        ];
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
