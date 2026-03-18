<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IpdManager
{
    public function generateAdmissionNumber()
    {
        $prefix = Setting::get('admission_prefix', 'ADM');
        $lastAdmission = Admission::latest('id')->first();
        $nextId = $lastAdmission ? $lastAdmission->id + 1 : 1;
        
        return $prefix . '-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public function admitPatient(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['admission_number'] = $this->generateAdmissionNumber();
            $data['created_by'] = Auth::id();
            $data['status'] = 'Admitted';
            
            $admission = Admission::create($data);
            
            // Mark bed as occupied
            $bed = Bed::findOrFail($data['bed_id']);
            $bed->update(['is_available' => false]);

            event(new \App\Events\IPD\PatientAdmitted($admission->load(['patient', 'bed.ward', 'doctor.user'])));
            
            return $admission;
        });
    }

    public function dischargePatient(Admission $admission, $notes = null)
    {
        return DB::transaction(function () use ($admission, $notes) {
            $admission->update([
                'discharge_date' => now(),
                'status' => 'Discharged',
                'notes' => $notes ?: $admission->notes
            ]);
            
            // Free the bed
            $bed = $admission->bed;
            $bed->update(['is_available' => true]);
            
            return $admission;
        });
    }
}
