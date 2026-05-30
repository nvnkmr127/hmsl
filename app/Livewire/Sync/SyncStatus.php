<?php

namespace App\Livewire\Sync;

use Livewire\Component;
use App\Sync\Services\SyncEngine;
use App\Sync\Services\UpdateService;

class SyncStatus extends Component
{
    public bool $isSyncing = false;
    public string $lastSyncAt = '';
    public int $pendingChanges = 0;

    public function mount()
    {
        $this->lastSyncAt = cache('last_sync_at', 'Never');
        $this->pendingChanges = \App\Sync\Models\SyncOutbox::where('status', 'pending')->count();
    }

    #[\Livewire\Attributes\On('trigger-background-sync')]
    #[\Livewire\Attributes\On('trigger-update-check')]
    public function checkForUpdates(UpdateService $updateService)
    {
        $result = $updateService->checkForUpdates();
        if ($result['has_update'] ?? false) {
            // Reload the page so updated views/assets are served
            $this->dispatch('notify', ['message' => 'App updated to v' . ($result['server_version'] ?? '?') . '. Reloading...']);
            $this->js('setTimeout(() => window.location.reload(), 1500)');
        }
    }

    public function triggerSync(SyncEngine $engine)
    {
        $this->isSyncing = true;

        try {
            $results = $engine->performSync();
            $this->lastSyncAt = now()->toDateTimeString();
            cache(['last_sync_at' => $this->lastSyncAt]);
            $this->dispatch('notify', ['message' => "Sync complete: {$results['pushed']} pushed, {$results['pulled']} pulled."]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "Sync failed: " . $e->getMessage()]);
        }

        $this->isSyncing = false;
        $this->pendingChanges = \App\Sync\Models\SyncOutbox::where('status', 'pending')->count();
    }

    public function render()
    {
        return view('livewire.sync.sync-status');
    }
}
