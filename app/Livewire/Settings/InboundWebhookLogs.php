<?php

namespace App\Livewire\Settings;

use App\Models\InboundWebhook;
use Livewire\Component;
use Livewire\WithPagination;

class InboundWebhookLogs extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.settings.inbound-webhook-logs', [
            'logs' => InboundWebhook::latest()->paginate(10)
        ]);
    }
}
