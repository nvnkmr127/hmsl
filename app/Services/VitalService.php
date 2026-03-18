<?php

namespace App\Services;

use App\Models\PatientVital;
use Illuminate\Support\Facades\Auth;

class VitalService
{
    public function record(array $data)
    {
        if (isset($data['weight']) && isset($data['height']) && $data['height'] > 0) {
            $heightInMeters = $data['height'] / 100;
            $data['bmi'] = round($data['weight'] / ($heightInMeters * $heightInMeters), 1);
        }

        $data['recorded_by'] = Auth::id();

        return PatientVital::create($data);
    }

    public function getLatestForPatient(int $patientId)
    {
        return PatientVital::where('patient_id', '=', $patientId)
            ->latest()
            ->first();
    }

    public function getHistoryForPatient(int $patientId)
    {
        return PatientVital::where('patient_id', '=', $patientId)
            ->latest()
            ->get();
    }
}
