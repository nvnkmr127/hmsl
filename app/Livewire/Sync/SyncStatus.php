<?php

namespace App\Livewire\Sync;

use Livewire\Component;
use App\Sync\Services\SyncEngine;
use App\Sync\Services\SyncService;
use App\Sync\Services\UpdateService;
use Illuminate\Support\Facades\Process;

class SyncStatus extends Component
{
    public bool $isFullPage = false;
    public bool $isSyncing = false;
    public string $status = 'offline';
    public ?string $lastSync = null;
    public int $pendingChanges = 0;
    public bool $isOnline = false;
    public $conflicts = [];

    // For backward compatibility with layouts and javascript
    public string $lastSyncAt = '';

    protected $listeners = [
        'conflict-resolved' => 'loadStatus',
    ];

    public function mount()
    {
        $this->loadStatus();
    }

    public function loadStatus()
    {
        $syncStatusPath = storage_path('app/sync_status.json');
        if (file_exists($syncStatusPath)) {
            $data = json_decode(file_get_contents($syncStatusPath), true);
            if ($data) {
                $this->status = $data['status'] ?? 'offline';
                $this->lastSync = $data['last_sync'] ?? null;
                $this->pendingChanges = $data['pending_changes'] ?? 0;
                $this->isOnline = ($this->status !== 'offline');
            }
        } else {
            // Fallback defaults
            $this->status = 'offline';
            $this->lastSync = cache('last_sync_at') ?: null;
            $this->pendingChanges = \App\Sync\Models\SyncOutbox::whereIn('status', ['pending', 'failed'])
                ->where('retry_count', '<', 3)
                ->count();
            $this->isOnline = false;
        }

        // Backward compatibility human-readable property
        $this->lastSyncAt = $this->lastSync ? \Carbon\Carbon::parse($this->lastSync)->diffForHumans() : 'Never';

        // Load conflicts
        $this->conflicts = \App\Sync\Models\SyncConflict::where('resolution', 'pending')->get();

        $this->dispatch('sync-status-changed', isOnline: $this->isOnline);
    }

    public function pollStatus()
    {
        $this->loadStatus();
    }

    public function triggerSync()
    {
        $this->isSyncing = true;
        
        try {
            $php = PHP_BINARY;
            $process = Process::run("\"$php\" artisan sync:perform");
            
            if ($process->successful()) {
                $this->dispatch('notify', ['message' => 'Sync completed successfully!']);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Sync failed: ' . $process->errorOutput()]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sync failed: ' . $e->getMessage()]);
        }
        
        $this->isSyncing = false;
        $this->loadStatus();
        $this->dispatch('conflict-resolved'); // Refresh sibling conflict lists
    }

    public function resolveConflict($conflictId, $resolution, SyncService $syncService)
    {
        try {
            $syncService->resolveConflict((int)$conflictId, $resolution);
            $this->dispatch('notify', ['message' => 'Conflict resolved successfully.']);
            $this->loadStatus();
            $this->dispatch('conflict-resolved');
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to resolve conflict: ' . $e->getMessage()]);
        }
    }

    #[\Livewire\Attributes\On('trigger-background-sync')]
    public function handleBackgroundSync()
    {
        $this->triggerSync();
    }

    #[\Livewire\Attributes\On('trigger-update-check')]
    public function checkForUpdates(UpdateService $updateService)
    {
        $result = $updateService->checkForUpdates();
        if ($result['has_update'] ?? false) {
            $this->dispatch('notify', ['message' => 'App updated to v' . ($result['server_version'] ?? '?') . '. Reloading...']);
            $this->js('setTimeout(() => window.location.reload(), 1500)');
        }
    }

    public function render()
    {
        return view('livewire.sync.sync-status');
    }
}
