<?php

namespace App\Livewire\Front;

use App\Models\Doctor;
use App\Models\Consultation;
use Livewire\Component;

class QueueMonitor extends Component
{
    public function render()
    {
        $doctors = Doctor::with(['user', 'department'])
            ->where(fn($q) => $q->where('is_active', '=', true))
            ->get();

        $consultationsByDoctor = Consultation::with('patient')
            ->whereIn('doctor_id', $doctors->pluck('id'))
            ->whereDate('consultation_date', date('Y-m-d'))
            ->whereIn('status', ['Ongoing', 'Pending'])
            ->orderBy('token_number')
            ->get()
            ->groupBy('doctor_id');

        $doctorStats = [];
        foreach ($doctors as $doctor) {
            $doctorConsultations = $consultationsByDoctor->get($doctor->id, collect());
            $ongoing = $doctorConsultations->firstWhere('status', 'Ongoing');
            $next = optional($doctorConsultations->firstWhere('status', 'Pending'))->token_number;

            $doctorStats[] = [
                'doctor' => $doctor,
                'ongoing_token' => $ongoing ? $ongoing->token_number : '--',
                'ongoing_patient' => $ongoing ? $ongoing->patient->full_name : 'Waiting...',
                'next_token' => $next ?: '--'
            ];
        }

        return view('livewire.front.queue-monitor', [
            'stats' => $doctorStats
        ]);
    }
}
