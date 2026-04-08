<?php

namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\Request;

class IpdController extends Controller
{
    public function show(Admission $admission)
    {
        $admission->load([
            'patient',
            'bed.ward',
            'doctor.user',
            'ipdNotes' => fn($q) => $q->latest()->limit(10),
            'ipdVitals' => fn($q) => $q->latest()->limit(5),
            'ipdMedications' => fn($q) => $q->where('status', 'Active'),
            'labOrders.labTest',
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

        return view('pages.discharge.summary', compact('admission'));
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
}
