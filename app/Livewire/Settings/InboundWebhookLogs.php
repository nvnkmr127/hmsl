<?php

namespace App\Livewire\Settings;

use App\Models\InboundWebhook;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class InboundWebhookLogs extends Component
{
    use WithPagination;

    #[Url]
    public $sourceSlug = null;
    
    public $search = '';
    public $status = 'all';
    public $selectedLogId = null;

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }

    public function showDetails($id)
    {
        $this->selectedLogId = $id;
    }

    public function closeDetails()
    {
        $this->selectedLogId = null;
    }

    public function retryProcessing($id)
    {
        $webhook = InboundWebhook::findOrFail($id);
        
        try {
            $webhook->update(['status' => 'pending', 'attempt_count' => 0, 'error_message' => null]);
            \App\Jobs\ProcessInboundWebhookJob::dispatch($webhook);
            \App\Models\AuditLog::log('webhook.inbound_retry', $webhook, [], [], ['webhook']);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Processing attempt queued.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to queue: ' . $e->getMessage()]);
        }
    }

    public function replay($id)
    {
        $webhook = InboundWebhook::findOrFail($id);
        
        // Clone for replay to keep history
        $clone = $webhook->replicate();
        $clone->status = 'pending';
        $clone->attempt_count = 0;
        $clone->error_message = null;
        $clone->correlation_id = (string) \Illuminate\Support\Str::uuid();
        $clone->save();

        try {
            \App\Jobs\ProcessInboundWebhookJob::dispatch($clone);
            \App\Models\AuditLog::log('webhook.inbound_replay', $clone, ['original_id' => $webhook->id], [], ['webhook']);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Replay initiated with new correlation ID: ' . substr($clone->correlation_id, 0, 8)]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to replay: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $logs = InboundWebhook::latest()
            ->when($this->sourceSlug, fn($q) => $q->where('source', $this->sourceSlug))
            ->when($this->status !== 'all', fn($q) => $q->where('status', $this->status))
            ->when($this->search, function($q) {
                $q->where('source', 'like', "%{$this->search}%")
                  ->orWhere('external_id', 'like', "%{$this->search}%")
                  ->orWhere('correlation_id', 'like', "%{$this->search}%")
                  ->orWhere('error_message', 'like', "%{$this->search}%");
            })
            ->paginate(20);

        return view('livewire.settings.inbound-webhook-logs', [
            'logs' => $logs,
            'selectedLog' => $this->selectedLogId ? InboundWebhook::find($this->selectedLogId) : null
        ]);
    }
}
