<?php

namespace App\Livewire\Settings;

use App\Models\WebhookLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class WebhookLogs extends Component
{
    use WithPagination;
    
    #[Url]
    public $endpointId = null;

    public $search = '';
    public $status = 'all';
    public $selectedLogId = null;
    public $selectedLogs = [];

    public function mount()
    {
        if (request()->has('logId')) {
            $this->selectedLogId = request()->logId;
        }
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }

    public function bulkRetry()
    {
        if (empty($this->selectedLogs)) return;

        $logs = WebhookLog::whereIn('id', $this->selectedLogs)->get();
        $count = 0;

        foreach ($logs as $log) {
            if ($log->endpoint) {
                \App\Jobs\SendWebhookJob::dispatch($log->endpoint, $log->payload, $log->attempt_number + 1, $log->correlation_id);
                $count++;
            }
        }

        $this->selectedLogs = [];
        $this->dispatch('notify', type: 'success', message: "Successfully queued {$count} log(s) for retry.");
    }

    public function showDetails($id)
    {
        $this->selectedLogId = $id;
    }

    public function closeDetails()
    {
        $this->selectedLogId = null;
    }

    public function render()
    {
        $selectedLog = $this->selectedLogId ? WebhookLog::with('endpoint')->find($this->selectedLogId) : null;
        
        $logs = WebhookLog::with('endpoint')
            ->when($this->endpointId, fn($q) => $q->where('webhook_endpoint_id', $this->endpointId))
            ->when($this->status !== 'all', fn($q) => $q->where('status', $this->status))
            ->when($this->search, function($q) {
                $q->where('event_name', 'like', "%{$this->search}%")
                  ->orWhere('delivery_id', 'like', "%{$this->search}%")
                  ->orWhere('correlation_id', 'like', "%{$this->search}%")
                  ->orWhere('response_status', 'like', "%{$this->search}%")
                  ->orWhereHas('endpoint', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(20);

        return view('livewire.settings.webhook-logs', [
            'logs' => $logs,
            'selectedLog' => $selectedLog
        ]);
    }

    public function retry($id)
    {
        $log = WebhookLog::findOrFail($id);
        
        if ($log->endpoint) {
            try {
                \App\Jobs\SendWebhookJob::dispatch($log->endpoint, $log->payload, $log->attempt_number + 1, $log->correlation_id);
                $this->dispatch('notify', type: 'success', message: 'Retry attempt queued successfully.');
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'error', message: 'Retry attempt failed: ' . $e->getMessage());
            }
        } else {
            $this->dispatch('notify', type: 'error', message: 'Endpoint no longer exists.');
        }
    }
}
