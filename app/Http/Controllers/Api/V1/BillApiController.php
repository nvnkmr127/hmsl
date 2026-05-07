<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BillResource;
use App\Models\Bill;
use Illuminate\Http\Request;

class BillApiController extends Controller
{
    /**
     * Display a listing of bills.
     */
    public function index(Request $request)
    {
        $bills = Bill::with(['patient'])
            ->when($request->patient_id, fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return BillResource::collection($bills);
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill)
    {
        return new BillResource($bill->load(['patient', 'items', 'payments']));
    }
}
