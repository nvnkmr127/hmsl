<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LabResultResource;
use App\Models\LabResult;
use Illuminate\Http\Request;

class LabApiController extends Controller
{
    /**
     * Display a listing of lab results.
     */
    public function index(Request $request)
    {
        $results = LabResult::with(['patient', 'resultedBy'])
            ->when($request->patient_id, fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return LabResultResource::collection($results);
    }

    /**
     * Display the specified lab result.
     */
    public function show(LabResult $lab)
    {
        return new LabResultResource($lab->load(['patient', 'resultedBy', 'labOrder']));
    }
}
