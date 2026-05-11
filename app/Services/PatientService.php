<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public function generateUHID()
    {
        return DB::transaction(function () {
            // 1. Get current counter from settings with lock
            $setting = Setting::where('key', 'next_uhid')->lockForUpdate()->first();
            
            if (!$setting) {
                // Initialize if it doesn't exist. 
                // We jump to 1150 to move past the problematic 1122 entry reported by the user.
                $currentMax = (int) Patient::withTrashed()->whereRaw('uhid REGEXP "^[0-9]+$"')->max('uhid');
                $startValue = max($currentMax, 1150);
                
                $setting = Setting::create([
                    'key' => 'next_uhid',
                    'value' => (string) ($startValue + 1),
                    'group' => 'system'
                ]);
                
                $nextId = $startValue;
            } else {
                $nextId = (int) $setting->value;
                $setting->update(['value' => (string) ($nextId + 1)]);
            }

            // Clear cache for this setting
            \Illuminate\Support\Facades\Cache::forget("setting.next_uhid");

            // 2. Apply prefix if configured
            $prefix = Setting::get('uhid_prefix', '');
            
            return $prefix . $nextId;
        });
    }

    public function getAll(?string $search = null, array $filters = [], string $sortBy = 'latest')
    {
        return Patient::query()
            ->with(['latestConsultation.doctor'])
            ->when($search, fn($q) => $q->search($search))
            ->when($filters['gender'] ?? null, fn($q) => $q->where('gender', $filters['gender']))

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
