<?php

namespace App\Sync\Jobs;

use App\Sync\Services\SyncEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PerformBackgroundSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SyncEngine $engine): void
    {
        if (!config('sync.enabled')) {
            Log::info('Background Sync Skipped: synchronization is disabled.');
            return;
        }

        try {
            Log::info('Background Sync Started.');
            
            $results = $engine->performSync();
            
            Log::info('Background Sync Completed.', $results);
            
            // Dispatch event for Livewire components to listen to
            // event(new \App\Sync\Events\SyncCompleted($results));
            
        } catch (\Exception $e) {
            Log::error('Background Sync Failed: ' . $e->getMessage());
            
            // If it's a connection error, we might want to retry later
            // if ($this->attempts() < 3) {
            //     $this->release(60);
            // }
        }
    }
}
