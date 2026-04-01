<?php

namespace App\Livewire\Master;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;

class UserForm extends Component
{
    public $isEditing = false;
    public $userId;
    public $name;
    public $email;
    public $password;
    public $role;

    #[On('create-user')]
    public function create()
    {
        $this->reset(['name', 'email', 'password', 'role', 'userId', 'isEditing']);
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'user-modal');
    }

    #[On('edit-user')]
    public function edit($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->userId = $id;
        
        $user = User::findOrFail($id);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name;

        $this->dispatch('open-modal', name: 'user-modal');
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'role' => 'required|exists:roles,name',
        ];

        if (!$this->isEditing) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        $this->validate($rules);

        if ($this->isEditing) {
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }

            $user->syncRoles([$this->role]);
            $message = 'User updated successfully!';
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $user->assignRole($this->role);
            $message = 'User created successfully!';
        }

        $this->dispatch('close-modal', name: 'user-modal');
        $this->dispatch('user-saved');
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.master.user-form', compact('roles'));
    }
}
