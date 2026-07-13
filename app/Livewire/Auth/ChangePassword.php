<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Component
{
    public string $password = '';
    public string $password_confirmation = '';

    public function changePassword()
    {
        $this->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        session()->flash('message', 'Password updated successfully!');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.change-password')->layout('layouts.app');
    }
}
