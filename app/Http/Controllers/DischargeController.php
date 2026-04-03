<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;

class DischargeController extends Controller
{
    protected $ipdService;
    
    public function __construct(\App\Services\IpdService $ipdService)
    {
        $this->ipdService = $ipdService;
    }

    /**
     * Display the discharge management dashboard.
     */
    public function index()
    {
        return view('pages.discharge.index');
    }

    /**
     * Display the discharge summary for a specific admission.
     */
    public function show(Admission $admission)
    {
        $admission = $this->ipdService->getDischargeDetails($admission);
        return view('pages.discharge.summary', compact('admission'));
    }

    /**
     * Print the discharge summary.
     */
    public function print(Admission $admission)
    {
        $admission = $this->ipdService->getDischargeDetails($admission);
        return view('pages.discharge.summary-print', compact('admission'));
    }
}
