<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function revenue()
    {
        return view('pages.reports.revenue');
    }

    public function index()
    {
        return redirect()->route('reports.revenue');
    }
}
