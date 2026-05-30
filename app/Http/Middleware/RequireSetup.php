<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSetup
{
    public function handle(Request $request, Closure $next)
    {
        $isConfigured = !empty(config('sync.server_url'));
        $isSetupRoute = $request->routeIs('setup.*') || $request->is('setup*');
        $isAuthRoute  = $request->routeIs('login') || $request->routeIs('logout') || $request->is('auth/*');
        $isApiRoute   = $request->is('api/*');

        if (!$isConfigured && !$isSetupRoute && !$isAuthRoute && !$isApiRoute) {
            return redirect()->route('setup');
        }

        return $next($request);
    }
}
