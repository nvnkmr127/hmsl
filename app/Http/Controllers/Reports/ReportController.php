<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of available reports.
     */
    public function index()
    {
        $reportGroups = [
            'Financial Flow' => [
                ['title' => 'Revenue Analytics', 'route' => 'reports.revenue', 'icon' => 'revenue', 'desc' => 'Comprehensive financial performance dashboard.'],
                ['title' => 'Outstanding Receivables', 'route' => 'reports.dues', 'icon' => 'dues', 'desc' => 'Track unpaid balances and collection health.'],
                ['title' => 'Discount Audit', 'route' => 'reports.discounts', 'icon' => 'discount', 'desc' => 'Traceability for all price adjustments and authorizers.'],
            ],
            'Patient Flow' => [
                ['title' => 'Inpatient Intelligence', 'route' => 'reports.ipd', 'icon' => 'bed', 'desc' => 'Detailed analytics on IPD admissions and wards.'],
                ['title' => 'Visit Intelligence', 'route' => 'reports.visits', 'icon' => 'visit', 'desc' => 'Detailed analytics on OPD volume and revisit patterns.'],
                ['title' => 'Registration Metrics', 'route' => 'reports.registrations', 'icon' => 'patient', 'desc' => 'Patient demographics and registration trends.'],
            ],
            'Clinical Excellence' => [
                ['title' => 'Consultation Stats', 'route' => 'reports.doctor-consults', 'icon' => 'doctor', 'desc' => 'Doctor-wise performance and caseload analysis.'],
            ]
        ];

        return view('pages.reports.index', compact('reportGroups'));
    }

    public function revenue()
    {
        return view('pages.reports.revenue');
    }

    public function visits()
    {
        return view('pages.reports.visits');
    }

    public function ipd()
    {
        return view('pages.reports.ipd');
    }
    
    public function registrations()
    {
        return view('pages.reports.registrations');
    }

    public function dues()
    {
        return view('pages.reports.dues');
    }

    public function doctorConsults()
    {
        return view('pages.reports.doctor-consults');
    }
}
