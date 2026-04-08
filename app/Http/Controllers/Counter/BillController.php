<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

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
}
