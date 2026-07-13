<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLoginController extends Controller
{
    public function login(Request $request)
    {
        if (!app()->isLocal()) {
            abort(403, 'Auto-login is only available in the local environment.');
        }

        $role = $request->query('role');
        
        $emailMap = [
            'admin' => 'admin@hospital.com',
            'doctor' => 'doctor@hospital.com',
            'counter' => 'counter@hospital.com',
            'nurse' => 'nurse@hospital.com',
        ];

        $email = $emailMap[$role] ?? null;

        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard');
            }
        }

        return redirect('/login')->with('error', 'Auto-login failed. User not found.');
    }
}
