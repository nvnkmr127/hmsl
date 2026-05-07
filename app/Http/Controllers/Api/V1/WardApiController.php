<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WardResource;
use App\Models\Ward;
use Illuminate\Http\Request;

class WardApiController extends Controller
{
    /**
     * Display a listing of wards.
     */
    public function index(Request $request)
    {
        $wards = Ward::with(['beds'])
            ->where('is_active', true)
            ->get();

        return WardResource::collection($wards);
    }
}
