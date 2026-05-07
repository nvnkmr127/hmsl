<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $services = Service::query()
            ->where('is_active', true)
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate($request->per_page ?? 15);

        return ServiceResource::collection($services);
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        return new ServiceResource($service);
    }
}
