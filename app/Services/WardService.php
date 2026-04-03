<?php

namespace App\Services;

use App\Models\Ward;
use App\Models\Bed;

class WardService
{
    public function getAllWards()
    {
        return Ward::with('beds')->latest()->get();
    }

    public function createWard(array $data)
    {
        $ward = Ward::create($data);
        $this->generateBeds($ward);
        return $ward;
    }

    public function updateWard(Ward $ward, array $data)
    {
        $oldCapacity = $ward->capacity;
        $ward->update($data);
        
        if ($oldCapacity != $ward->capacity) {
            $this->syncBeds($ward);
        }
        
        return $ward;
    }

    public function generateBeds(Ward $ward)
    {
        for ($i = 1; $i <= $ward->capacity; $i++) {
            Bed::create([
                'ward_id' => $ward->id,
                'bed_number' => $ward->name . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'is_available' => true
            ]);
        }
    }

    public function syncBeds(Ward $ward)
    {
        $currentBedsCount = $ward->beds()->count();
        
        if ($ward->capacity > $currentBedsCount) {
            // Add more beds
            for ($i = $currentBedsCount + 1; $i <= $ward->capacity; $i++) {
                Bed::create([
                    'ward_id' => $ward->id,
                    'bed_number' => $ward->name . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'is_available' => true
                ]);
            }
        } elseif ($ward->capacity < $currentBedsCount) {
            // Remove extra beds (only if they are available/empty)
            $ward->beds()
                ->where('is_available', true)
                ->orderBy('id', 'desc')
                ->limit($currentBedsCount - $ward->capacity)
                ->delete();
        }
    }

    public function deleteWard(Ward $ward)
    {
        return $ward->delete();
    }
}
