<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionApiController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request)
    {
        $prescriptions = Prescription::with(['patient', 'doctor'])
            ->when($request->patient_id, fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->doctor_id, fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return PrescriptionResource::collection($prescriptions);
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription)
    {
        return new PrescriptionResource($prescription->load(['patient', 'doctor']));
    }
}
