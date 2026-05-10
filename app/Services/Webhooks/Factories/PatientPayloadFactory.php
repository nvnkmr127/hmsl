<?php

namespace App\Services\Webhooks\Factories;

use App\Models\Patient;

class PatientPayloadFactory
{
    public static function forPatient(Patient $patient): array
    {
        return [
            'uhid' => $patient->uhid,
            'name' => "{$patient->first_name} {$patient->last_name}",
            'gender' => $patient->gender,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'date_of_birth' => $patient->date_of_birth,
            'address' => [
                'street' => $patient->address,
                'city' => $patient->city,
                'state' => $patient->state,
                'pincode' => $patient->pincode,
            ],
            'created_at' => $patient->created_at->toISOString(),
        ];
    }
}
