<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentApiController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $departments = Department::query()
            ->where('is_active', true)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->get();

        return DepartmentResource::collection($departments);
    }
}
