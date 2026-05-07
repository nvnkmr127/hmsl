<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\VitalResource;
use App\Models\PatientVital;
use Illuminate\Http\Request;

class VitalApiController extends Controller
{
    /**
     * Display a listing of vitals.
     */
    public function index(Request $request)
    {
        $vitals = PatientVital::with(['patient', 'recorder'])
            ->when($request->patient_id, fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->consultation_id, fn($q) => $q->where('consultation_id', $request->consultation_id))
            ->when($request->admission_id, fn($q) => $q->where('admission_id', $request->admission_id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return VitalResource::collection($vitals);
    }

    /**
     * Display the specified vitals.
     */
    public function show(PatientVital $vital)
    {
        return new VitalResource($vital->load(['patient', 'recorder']));
    }
}
