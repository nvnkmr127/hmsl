<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AdmissionResource;
use App\Models\Admission;
use Illuminate\Http\Request;

class AdmissionApiController extends Controller
{
    /**
     * Display a listing of admissions.
     */
    public function index(Request $request)
    {
        $admissions = Admission::with(['patient', 'doctor', 'bed.ward'])
            ->when($request->patient_id, fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('admission_date')
            ->paginate($request->per_page ?? 15);

        return AdmissionResource::collection($admissions);
    }

    /**
     * Display the specified admission.
     */
    public function show(Admission $admission)
    {
        return new AdmissionResource($admission->load(['patient', 'doctor', 'bed.ward', 'vitals', 'diagnoses']));
    }
}
