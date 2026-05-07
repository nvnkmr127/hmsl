<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MedicineResource;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineApiController extends Controller
{
    /**
     * Display a listing of medicines.
     */
    public function index(Request $request)
    {
        $medicines = Medicine::query()
            ->where('is_active', true)
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('generic_name', 'like', "%{$request->search}%");
            })
            ->paginate($request->per_page ?? 15);

        return MedicineResource::collection($medicines);
    }

    /**
     * Display the specified medicine.
     */
    public function show(Medicine $medicine)
    {
        return new MedicineResource($medicine);
    }
}
