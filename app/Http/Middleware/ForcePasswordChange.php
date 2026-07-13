<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (Hash::check('admin123', $user->password)) {
                // Exclude password change, logout, and Livewire update routes
                if (!$request->is('change-password*') && !$request->is('logout*') && !$request->is('livewire*')) {
                    return redirect()->route('password.change');
                }
            }
        }
        return $next($request);
    }
}
