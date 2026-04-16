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
            'Financial' => [
                ['title' => 'Revenue Dashboard', 'route' => 'reports.revenue', 'icon' => 'revenue', 'desc' => 'Daily and monthly revenue performance analytics.'],
                ['title' => 'Outstanding Dues', 'route' => 'reports.dues', 'icon' => 'dues', 'desc' => 'Track unpaid and partially paid bills.'],
                ['title' => 'Discount Audit', 'route' => 'reports.discounts', 'icon' => 'discount', 'desc' => 'Detailed trail of all bill discounts and authorizers.'],
            ],
            'Clinical' => [
                ['title' => 'Patient Visits', 'route' => 'reports.visits', 'icon' => 'visit', 'desc' => 'Comprehensive log of OPD visits and status.'],
                ['title' => 'Doctor Wise Consults', 'route' => 'reports.doctor-consults', 'icon' => 'doctor', 'desc' => 'Performance breakdown by doctor.'],
            ],
            'Operations' => [
                ['title' => 'Inventory Status', 'route' => 'reports.inventory', 'icon' => 'inventory', 'desc' => 'Current stock levels and low stock alerts.'],
                ['title' => 'Lab Utilization', 'route' => 'reports.lab', 'icon' => 'lab', 'desc' => 'Usage statistics for laboratory services.'],
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

    public function dues()
    {
        return view('pages.reports.dues');
    }

    public function doctorConsults()
    {
        return view('pages.reports.doctor-consults');
    }

    public function inventory()
    {
        return view('pages.reports.inventory');
    }

    public function lab()
    {
        return view('pages.reports.lab');
    }
}
