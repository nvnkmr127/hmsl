<?php

namespace App\Events\System;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailySummaryGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $summary;

    /**
     * Create a new event instance.
     */
    public function __construct(array $summary)
    {
        $this->summary = $summary;
    }
}
