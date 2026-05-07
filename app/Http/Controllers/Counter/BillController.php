<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;

class BillController extends Controller
{
    /**
     * Display the billing index page.
     */
    public function index()
    {
        return view('pages.billing.index');
    }

    /**
     * Print a specific bill.
     */
    public function print(Bill $bill)
    {
        $bill->load(['patient', 'items', 'consultation.doctor.department', 'admission.bed.ward', 'admission.doctor', 'creator']);
        return view('pages.counter.bill-print', compact('bill'));
    }

    /**
     * Generate PDF for a specific bill.
     */
    public function pdf(Request $request, Bill $bill)
    {
        // If it's a signed request, we skip auth check (handled by middleware)
        if (!$request->hasValidSignature() && !auth()->check()) {
            abort(403);
        }

        $bill->load(['patient', 'items', 'consultation.doctor.department', 'admission.bed.ward', 'admission.doctor', 'creator']);
        
        $pdf = Pdf::loadView('pages.counter.bill-pdf', compact('bill'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream("Bill-{$bill->bill_number}.pdf");
    }
}
