<?php

namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\Request;

class IpdController extends Controller
{
    public function show(Admission $admission)
    {
        // Automatically sync bill estimate only if it doesn't have a generated bill or if we haven't started discharge
        if (!$admission->finalBill) {
            app(\App\Services\IpdService::class)->ensureFinalBill($admission);
        }

        $admission->load([
            'patient',
            'bed.ward',
            'doctor.user',
            'ipdNotes' => fn($q) => $q->latest()->limit(10),
            'ipdVitals' => fn($q) => $q->latest()->limit(5),
            'ipdMedications' => fn($q) => $q->where('status', 'Active'),
            'labOrders.labTest',
            'finalBill.items',
            'diagnoses',
        ]);

        return view('pages.ipd.show', compact('admission'));
    }

    public function dischargeSummary(Admission $admission)
    {
        $admission->load([
            'patient',
            'bed.ward',
            'doctor.user',
            'dischargeSummary.medications',
            'ipdNotes' => fn($q) => $q->latest()->limit(10),
            'ipdVitals' => fn($q) => $q->latest()->limit(5),
            'ipdMedications' => fn($q) => $q->where('status', 'Active'),
            'labOrders.labTest',
            'diagnoses',
        ]);

        return view('pages.discharge.form', compact('admission'));
    }

    public function printSummary(Admission $admission)
    {
        $admission->load([
            'patient',
            'bed.ward',
            'doctor.user',
            'dischargeSummary.medications',
            'ipdNotes' => fn($q) => $q->latest()->limit(10),
            'ipdVitals' => fn($q) => $q->latest()->limit(5),
            'ipdMedications',
            'labOrders.labTest',
            'finalBill.items',
            'diagnoses',
        ]);

        return view('pages.discharge.summary-print', compact('admission'));
    }

    public function printCaseSheet(Admission $admission)
    {
        $admission->load([
            'patient',
            'bed.ward',
            'doctor.user',
            'ipdVitals' => fn($q) => $q->oldest()->limit(1),
        ]);

        return view('pages.ipd.case-sheet-print', compact('admission'));
    }

    public function printBill(Admission $admission)
    {
        if (!$admission->finalBill) {
            app(\App\Services\IpdService::class)->ensureFinalBill($admission);
            $admission->load('finalBill');
        }

        if ($admission->finalBill) {
            return redirect()->route('billing.bills.print', $admission->finalBill->id);
        }

        abort(404, 'Bill not found');
    }
}
