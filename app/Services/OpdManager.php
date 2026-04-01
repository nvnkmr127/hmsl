<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OpdManager
{
    public function generateToken(int $doctorId, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        
        $lastToken = Consultation::where('doctor_id', '=', $doctorId)
            ->whereDate('consultation_date', $date)
            ->max('token_number');
            
        return ($lastToken ?: 0) + 1;
    }

    public function bookAppointment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['consultation_date'] = $data['consultation_date'] ?: date('Y-m-d');
            $data['token_number'] = $this->generateToken($data['doctor_id'], $data['consultation_date']);
            $data['valid_upto'] = $data['valid_upto'] ?? date('Y-m-d', strtotime($data['consultation_date'] . ' +7 days'));
            
            // Auto-fetch fee from doctor profile if not provided
            if (!isset($data['fee'])) {
                $doctor = Doctor::findOrFail($data['doctor_id']);
                $data['fee'] = $doctor->consultation_fee;
            }

            return Consultation::create($data);
        });
    }

    public function getDailyQueue(int $doctorId, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        return Consultation::with('patient')
            ->where(fn($q) => $q->where('doctor_id', '=', $doctorId))
            ->whereDate('consultation_date', $date)
            ->orderBy('token_number')
            ->get();
    }

    public function updateStatus(Consultation $consultation, string $status)
    {
        $consultation->update(['status' => $status]);
        
        if ($status === 'Completed') {
            event(new \App\Events\OPD\ConsultationCompleted($consultation->load(['patient', 'doctor.user'])));
        }
        
        return $consultation;
    }

    public function markAsPaid(Consultation $consultation)
    {
        $consultation->update(['payment_status' => 'Paid']);
        return $consultation;
    }
}
