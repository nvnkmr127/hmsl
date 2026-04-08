<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\RedirectResponse;
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

    public function export(Request $request)
    {
        $date = now()->format('Ymd_His');

        $rows = Admission::with(['patient', 'bed.ward', 'doctor'])
            ->where('status', 'Admitted')
            ->latest('admission_date')
            ->get()
            ->map(function (Admission $a) {
                return [
                    $a->admission_number,
                    $a->patient?->uhid,
                    $a->patient?->full_name,
                    $a->admission_date?->format('Y-m-d H:i'),
                    $a->bed?->ward?->name,
                    $a->bed?->bed_number,
                    $a->doctor?->full_name,
                    $a->status,
                ];
            })
            ->all();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Admission No', 'UHID', 'Patient', 'Admitted At', 'Ward', 'Bed', 'Doctor', 'Status']);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, "pending_discharges_{$date}.csv", ['Content-Type' => 'text/csv']);
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

    public function generateFinalBill(Admission $admission): RedirectResponse
    {
        $this->ipdService->ensureFinalBill($admission);

        return redirect()
            ->route('discharge.summary', ['admission' => $admission->id])
            ->with('status', 'Final bill generated.');
    }
}
