<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Str;

class ApiTokenManager extends Component
{
    public $tokenName;
    public $plainTextToken;

    public function createToken()
    {
        $this->validate([
            'tokenName' => 'required|string|max:255',
        ]);

        $token = auth()->user()->createToken($this->tokenName);
        $this->plainTextToken = $token->plainTextToken;
        $this->tokenName = '';
        
        $this->dispatch('token-created');
    }

    public function deleteToken($tokenId)
    {
        auth()->user()->tokens()->where('id', $tokenId)->delete();
    }

    public function render()
    {
        return view('livewire.settings.api-token-manager', [
            'tokens' => auth()->user()->tokens
        ]);
    }
}
