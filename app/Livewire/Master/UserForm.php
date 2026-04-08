<?php

namespace App\Livewire\Master;

use App\Models\HospitalOwner;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

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

        $ownerUserId = HospitalOwner::ownerUser()?->id;
        if ($ownerUserId && $this->isEditing && (int) $ownerUserId === (int) $this->userId && $this->role !== 'doctor_owner') {
            throw ValidationException::withMessages([
                'role' => 'The hospital owner role cannot be removed.',
            ]);
        }

        if ($this->role === 'doctor_owner') {
            $existingOwner = User::query()
                ->whereHas('roles', fn($q) => $q->where('name', 'doctor_owner'))
                ->when($this->isEditing, fn($q) => $q->where('id', '!=', $this->userId))
                ->first();

            if ($existingOwner) {
                throw ValidationException::withMessages([
                    'role' => 'Only one doctor owner is allowed.',
                ]);
            }
        }

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
        $ownerUserId = HospitalOwner::ownerUser()?->id;

        $roles = Role::query()
            ->when($ownerUserId && !$this->isEditing, fn($q) => $q->where('name', '!=', 'doctor_owner'))
            ->when($ownerUserId && $this->isEditing && (int) $ownerUserId !== (int) $this->userId, fn($q) => $q->where('name', '!=', 'doctor_owner'))
            ->get();

        return view('livewire.master.user-form', compact('roles'));
    }
}
