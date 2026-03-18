<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $patient_id = $request->get('patient_id');
        return view('pages.counter.opd', compact('patient_id'));
    }

    public function print($id)
    {
        $consultation = \App\Models\Consultation::with(['patient', 'doctor.department'])->findOrFail($id);
        return view('pages.counter.opd-slip', compact('consultation'));
    }
}
