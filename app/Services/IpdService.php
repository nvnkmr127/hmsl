<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IpdService
{
    /**
     * Generate a unique admission number.
     */
    public function generateAdmissionNumber(): string
    {
        $prefix = \App\Models\Setting::get('admission_prefix', 'ADM');
        $lastAdmission = Admission::latest('id')->first();
        $nextId = $lastAdmission ? $lastAdmission->id + 1 : 1;
        
        return sprintf('%s-%s-%05d', $prefix, now()->format('Y'), $nextId);
    }

    /**
     * Handle the patient admission process.
     */
    public function admitPatient(array $data): Admission
    {
        return DB::transaction(function () use ($data) {
            $data['admission_number'] = $this->generateAdmissionNumber();
            $data['created_by'] = Auth::id();
            $data['status'] = 'Admitted';
            $data['admission_date'] = $data['admission_date'] ?? now()->toDateTimeString();
            
            $admission = Admission::create($data);
            
            // Record vitals if provided
            if (isset($data['weight']) || isset($data['height'])) {
                \App\Models\PatientVital::create([
                    'patient_id' => $admission->patient_id,
                    'admission_id' => $admission->id,
                    'weight' => $data['weight'] ?? null,
                    'height' => $data['height'] ?? null,
                    'recorded_by' => Auth::id(),
                ]);
            }

            // Mark bed as occupied
            $bed = Bed::findOrFail($data['bed_id']);
            $bed->update(['is_available' => false]);

            event(new \App\Events\IPD\PatientAdmitted($admission->load(['patient', 'bed.ward', 'doctor.user'])));
            
            return $admission;
        });
    }

    /**
     * Handle the patient discharge process.
     */
    public function dischargePatient(Admission $admission, ?string $notes = null): Admission
    {
        return DB::transaction(function () use ($admission, $notes) {
            $admission->update([
                'discharge_date' => now(),
                'status' => 'Discharged',
                'notes' => $notes ?: $admission->notes
            ]);
            
            // Free the bed
            $admission->bed()->update(['is_available' => true]);
            
            return $admission;
        });
    }

    /**
     * Fetch the detailed admission record for discharge summary/print.
     * Consolidates eager loading to the service layer.
     */
    public function getDischargeDetails(Admission $admission): Admission
    {
        return $admission->load([
            'patient', 
            'doctor.user', 
            'bed.ward', 
            'vitals', 
            'medications',
            'labOrders.labTest'
        ]);
    }
}
