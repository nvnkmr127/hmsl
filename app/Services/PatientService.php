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
                $currentMax = $this->getMaxNumericUHID();
                $nextId = max($currentMax + 1, 1150);
                
                $setting = Setting::create([
                    'key' => 'next_uhid',
                    'value' => (string) ($nextId + 1),
                    'group' => 'system'
                ]);
            } else {
                $currentMax = $this->getMaxNumericUHID();
                $nextId = (int) $setting->value;
                if ($nextId <= $currentMax) {
                    $nextId = $currentMax + 1;
                }
                $setting->update(['value' => (string) ($nextId + 1)]);
            }

            // Clear cache for this setting
            \Illuminate\Support\Facades\Cache::forget("setting.next_uhid");

            // 2. Return the numeric ID (prefix removed for consistency with existing records)
            return (string) $nextId;
        });
    }

    private function getMaxNumericUHID()
    {
        $query = Patient::withTrashed();
        if (DB::connection()->getDriverName() === 'sqlite') {
            return (int) $query->whereRaw("uhid GLOB '[0-9]*' AND uhid NOT GLOB '*[^0-9]*'")
                ->max(DB::raw('CAST(uhid AS INTEGER)'));
        } else {
            return (int) $query->whereRaw('uhid REGEXP "^[0-9]+$"')->max('uhid');
        }
    }

    public function getAll(?string $search = null, array $filters = [], string $sortBy = 'latest', bool $onlyTrashed = false)
    {
        $query = $onlyTrashed ? Patient::onlyTrashed() : Patient::query();
        $dateFilterType = $filters['dateFilterType'] ?? 'registration';

        return $query
            ->with(['latestConsultation.doctor'])
            ->when($search, fn($q) => $q->search($search))
            ->when($filters['gender'] ?? null, fn($q) => $q->where('gender', $filters['gender']))
            ->when(!empty($filters['dateFrom']), function($q) use ($filters, $dateFilterType) {
                if ($dateFilterType === 'visit') {
                    $q->whereHas('consultations', fn($q2) => $q2->where('consultation_date', '>=', $filters['dateFrom'] . ' 00:00:00'));
                } else {
                    $q->where('created_at', '>=', $filters['dateFrom'] . ' 00:00:00');
                }
            })
            ->when(!empty($filters['dateTo']), function($q) use ($filters, $dateFilterType) {
                if ($dateFilterType === 'visit') {
                    $q->whereHas('consultations', fn($q2) => $q2->where('consultation_date', '<=', $filters['dateTo'] . ' 23:59:59'));
                } else {
                    $q->where('created_at', '<=', $filters['dateTo'] . ' 23:59:59');
                }
            })
            ->when($sortBy === 'alphabetic', fn($q) => $q->orderBy('first_name'))
            ->latest()
            ->paginate(10);
    }

    public function getStats(?string $dateFrom = null, ?string $dateTo = null, string $dateFilterType = 'registration')
    {
        $applyDateFilters = function($q) use ($dateFrom, $dateTo, $dateFilterType) {
            $q->when(!empty($dateFrom), function($q) use ($dateFrom, $dateFilterType) {
                if ($dateFilterType === 'visit') {
                    $q->whereHas('consultations', fn($q2) => $q2->where('consultation_date', '>=', $dateFrom . ' 00:00:00'));
                } else {
                    $q->where('created_at', '>=', $dateFrom . ' 00:00:00');
                }
            })
            ->when(!empty($dateTo), function($q) use ($dateTo, $dateFilterType) {
                if ($dateFilterType === 'visit') {
                    $q->whereHas('consultations', fn($q2) => $q2->where('consultation_date', '<=', $dateTo . ' 23:59:59'));
                } else {
                    $q->where('created_at', '<=', $dateTo . ' 23:59:59');
                }
            });
        };

        $query = Patient::query();
        $applyDateFilters($query);

        $trashedQuery = Patient::onlyTrashed();
        $applyDateFilters($trashedQuery);

        $opBookings = \App\Models\Consultation::query()
            ->when(!empty($dateFrom), fn($q) => $q->where('consultation_date', '>=', $dateFrom . ' 00:00:00'))
            ->when(!empty($dateTo), fn($q) => $q->where('consultation_date', '<=', $dateTo . ' 23:59:59'))
            ->count();

        $ipBookings = \App\Models\Admission::query()
            ->when(!empty($dateFrom), fn($q) => $q->where('admission_date', '>=', $dateFrom . ' 00:00:00'))
            ->when(!empty($dateTo), fn($q) => $q->where('admission_date', '<=', $dateTo . ' 23:59:59'))
            ->count();

        return [
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('created_at', now())->count(),
            'male'  => (clone $query)->where('gender', 'Male')->count(),
            'female'=> (clone $query)->where('gender', 'Female')->count(),
            'trashed'=> $trashedQuery->count(),
            'op_bookings' => $opBookings,
            'ip_bookings' => $ipBookings,
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

    public function restore($id)
    {
        $patient = Patient::onlyTrashed()->findOrFail($id);
        $patient->restore();
        return $patient;
    }
}
