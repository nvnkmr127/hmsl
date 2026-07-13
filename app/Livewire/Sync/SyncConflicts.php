<?php

namespace App\Livewire\Sync;

use Livewire\Component;
use App\Sync\Models\SyncConflict;
use App\Sync\Services\SyncService;

class SyncConflicts extends Component
{
    public $conflicts = [];

    protected $listeners = [
        'conflict-resolved' => 'loadConflicts',
    ];

    public function mount()
    {
        $this->loadConflicts();
    }

    public function loadConflicts()
    {
        $this->conflicts = SyncConflict::where('resolution', 'pending')->get();
    }

    public function resolve($conflictId, $resolution, SyncService $syncService)
    {
        try {
            $syncService->resolveConflict((int)$conflictId, $resolution);
            $this->dispatch('notify', ['message' => 'Conflict resolved successfully.']);
            $this->dispatch('conflict-resolved'); // Refresh SyncStatus state
            $this->loadConflicts();
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to resolve conflict: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.sync.sync-conflicts');
    }
}
