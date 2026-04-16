<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppointmentResource;
use App\Models\Consultation;
use App\Services\OpdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentApiController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $appointments = Consultation::with(['patient', 'doctor', 'service'])
            ->when($request->patient_id, fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->doctor_id, fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('consultation_date', $request->date))
            ->latest('consultation_date')
            ->paginate($request->per_page ?? 15);

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a new appointment.
     */
    public function store(Request $request, OpdService $opdService)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'consultation_date' => 'required|date',
            'fee' => 'required|numeric|min:0',
            'payment_status' => 'required|in:Paid,Unpaid',
            'payment_method' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $consultation = $opdService->bookAppointment($request->all());
            return new AppointmentResource($consultation->load(['patient', 'doctor', 'service']));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show(Consultation $appointment)
    {
        return new AppointmentResource($appointment->load(['patient', 'doctor', 'service']));
    }

    /**
     * Cancel an appointment.
     */
    public function destroy(Consultation $appointment)
    {
        if ($appointment->status === 'Cancelled') {
            return response()->json(['error' => 'Appointment is already cancelled.'], 400);
        }

        $appointment->update(['status' => 'Cancelled']);
        return response()->json(['message' => 'Appointment cancelled successfully.']);
    }
}
