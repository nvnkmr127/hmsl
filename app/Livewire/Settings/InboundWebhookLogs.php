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
    public $selectedLogId = null;

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
        return view('livewire.settings.inbound-webhook-logs', [
            'logs' => InboundWebhook::latest()
                ->when($this->sourceSlug, fn($q) => $q->where('source', $this->sourceSlug))
                ->paginate(10),
            'selectedLog' => $this->selectedLogId ? InboundWebhook::find($this->selectedLogId) : null
        ]);
    }
}
