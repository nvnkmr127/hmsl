<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginLocal
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((app()->environment('local') || env('ALLOW_AUTOLOGIN', false)) && Auth::guest()) {
            $admin = User::where('email', 'admin@hospital.com')->first();
            if ($admin) {
                Auth::login($admin);
            }
        }

        return $next($request);
    }
}
