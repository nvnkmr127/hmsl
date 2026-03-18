<?php

namespace App\Livewire\Doctor;

use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DoctorDashboardStats extends Component
{
    public function render()
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return view('livewire.doctor.doctor-dashboard-stats', [
                'stats' => [
                    'total_appointments' => 0,
                    'monthly_earnings' => 0,
                    'pending_today' => 0,
                    'completed_today' => 0,
                ]
            ]);
        }

        $stats = [
            'total_appointments' => Consultation::where('doctor_id', $doctor->id)->count(),
            'monthly_earnings' => Bill::whereHas('consultation', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
            ->where('payment_status', 'Paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount'),
            'pending_today' => Consultation::where('doctor_id', $doctor->id)
                ->whereDate('consultation_date', today())
                ->where('status', 'Pending')
                ->count(),
            'completed_today' => Consultation::where('doctor_id', $doctor->id)
                ->whereDate('consultation_date', today())
                ->where('status', 'Completed')
                ->count(),
        ];

        return view('livewire.doctor.doctor-dashboard-stats', compact('stats'));
    }
}
