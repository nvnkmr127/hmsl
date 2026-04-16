<?php

namespace App\Livewire\Settings;

use App\Models\WebhookLog;
use Livewire\Component;
use Livewire\WithPagination;

class WebhookLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'all';

    public function render()
    {
        $logs = WebhookLog::with('endpoint')
            ->when($this->status !== 'all', fn($q) => $q->where('status', $this->status))
            ->when($this->search, function($q) {
                $q->where('event_name', 'like', "%{$this->search}%")
                  ->orWhere('response_status', 'like', "%{$this->search}%")
                  ->orWhereHas('endpoint', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(20);

        return view('livewire.settings.webhook-logs', [
            'logs' => $logs
        ]);
    }

    public function retry($id)
    {
        $log = WebhookLog::findOrFail($id);
        
        if ($log->endpoint) {
            \App\Jobs\SendWebhookJob::dispatch($log->endpoint, $log->payload, $log->attempt_number + 1);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Retry job dispatched.']);
        } else {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Endpoint no longer exists.']);
        }
    }
}
