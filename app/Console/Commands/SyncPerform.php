<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sync\Services\SyncEngine;

class SyncPerform extends Command
{
    protected $signature = 'sync:perform';
    protected $description = 'Manually trigger a data synchronization cycle';

    public function handle(SyncEngine $engine)
    {
        $this->info('Starting Synchronization...');

        try {
            $results = $engine->performSync();
            
            $this->table(['Metric', 'Count'], [
                ['Pushed Changes', $results['pushed']],
                ['Pulled Deltas', $results['pulled']],
            ]);

            $this->info('Synchronization Complete!');
        } catch (\Exception $e) {
            $this->error('Sync Failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
