<?php

namespace App\Livewire\Master;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserList extends Component
{
    use WithPagination;

    public $search = '';

    protected $listeners = ['user-saved' => '$refresh'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'You cannot delete yourself!']);
            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'User deleted successfully.']);
    }

    public function render()
    {
        $users = User::with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.master.user-list', compact('users'));
    }
}
