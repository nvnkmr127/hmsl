<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorApiController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(Request $request)
    {
        $doctors = Doctor::with(['department', 'user'])
            ->where('is_active', true)
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->search, function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%");
            })
            ->paginate($request->per_page ?? 15);

        return DoctorResource::collection($doctors);
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor)
    {
        return new DoctorResource($doctor->load(['department', 'user']));
    }
}
