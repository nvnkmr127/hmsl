<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $statsService;

    public function __construct(\App\Services\StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $metrics = $this->statsService->getDashboardMetrics($user->id);

        if ($user->hasRole('receptionist')) {
            return view('pages.dashboard.reception', compact('metrics'));
        }

        if ($user->hasRole('doctor')) {
            return view('pages.dashboard.doctor', compact('metrics'));
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

        if ($user->hasRole('doctor_owner')) {
            $ownerUser = HospitalOwner::ownerUser();
            if ($ownerUser && (int) $ownerUser->id !== (int) $user->id) {
                abort(403);
            }
            return view('pages.dashboard.admin', compact('metrics'));
        }

        abort(403);
    }
}
