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

        $doctorStats = [];
        foreach ($doctors as $doctor) {
            $ongoing = Consultation::with('patient')
                ->where(fn($q) => $q->where('doctor_id', '=', $doctor->id))
                ->whereDate('consultation_date', date('Y-m-d'))
                ->where(fn($q) => $q->where('status', '=', 'Ongoing'))
                ->first();

            $next = Consultation::where(fn($q) => $q->where('doctor_id', '=', $doctor->id))
                ->whereDate('consultation_date', date('Y-m-d'))
                ->where(fn($q) => $q->where('status', '=', 'Pending'))
                ->orderBy('token_number')
                ->value('token_number');

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
