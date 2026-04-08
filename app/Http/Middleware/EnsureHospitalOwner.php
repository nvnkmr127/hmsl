<?php

namespace App\Http\Middleware;

use App\Models\HospitalOwner;
use Closure;
use Illuminate\Http\Request;

class EnsureHospitalOwner
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $owner = HospitalOwner::ownerUser();
        if ($owner) {
            if ((int) $owner->id !== (int) $user->id) {
                abort(403);
            }

            return $next($request);
        }

        if (!$user->hasRole('doctor_owner')) {
            abort(403);
        }

        return $next($request);
    }
}

