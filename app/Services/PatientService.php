<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public function generateUHID()
    {
        // Use a lock to prevent race conditions during UHID generation
        // We look for the maximum numeric UHID to increment
        $maxUhid = (int) Patient::whereRaw('uhid REGEXP "^[0-9]+$"')
            ->lockForUpdate()
            ->max('uhid');
            
        $nextId = $maxUhid > 0 ? $maxUhid + 1 : 1001;
        
        return (string) $nextId;
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
        // Duplicate check
        $exists = Patient::where('phone', $data['phone'])
            ->where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'] ?? null)
            ->exists();

        if ($exists) {
            throw new \Exception('A patient with this name and phone number is already registered.');
        }

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
