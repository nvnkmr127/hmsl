<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $today = now()->toDateString();
        $doctor = Doctor::where('user_id', $user->id)->first();

        $metrics = [
            'opdToday' => Consultation::whereDate('consultation_date', $today)->count(),
            'opdPendingToday' => Consultation::whereDate('consultation_date', $today)->where('status', 'Pending')->count(),
            'ipdAdmitted' => Admission::where('status', 'Admitted')->count(),
            'billsToday' => Bill::whereDate('created_at', $today)->count(),
            'revenueToday' => Bill::whereDate('created_at', $today)->where('payment_status', 'Paid')->sum('total_amount'),
            'doctorPendingToday' => $doctor
                ? Consultation::whereDate('consultation_date', $today)->where('doctor_id', $doctor->id)->where('status', 'Pending')->count()
                : 0,
        ];

        if ($user->hasRole('receptionist')) {
            return view('pages.dashboard.reception', compact('metrics'));
        }

        if ($user->hasRole('nurse')) {
            return view('pages.dashboard.nurse', compact('metrics'));
        }

        if ($user->hasRole('accountant')) {
            return view('pages.dashboard.accountant', compact('metrics'));
        }

        if ($user->hasRole('lab_technician')) {
            return view('pages.dashboard.lab', compact('metrics'));
        }

        if ($user->hasRole('pharmacist')) {
            return view('pages.dashboard.pharmacy', compact('metrics'));
        }

        return view('pages.dashboard.admin', compact('metrics'));
    }
}
