<?php

namespace App\Livewire\Settings;

use App\Models\WebhookLog;
use App\Jobs\SendWebhookJob;
use Livewire\Component;
use Livewire\WithPagination;

class WebhookLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function retry($id)
    {
        $log = WebhookLog::findOrFail($id);
        SendWebhookJob::dispatch($log->endpoint, $log->payload);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Retry job queued.']);
    }

    public function render()
    {
        $logs = WebhookLog::with(['endpoint'])
            ->when($this->statusFilter, fn($q) => $q->where('status', '=', $this->statusFilter))
            ->where(fn($q) => $q->where('event_name', 'like', "%{$this->search}%")
                ->orWhereHas('endpoint', fn($eq) => $eq->where('name', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.settings.webhook-logs', [
            'logs' => $logs
        ]);
    }
}
