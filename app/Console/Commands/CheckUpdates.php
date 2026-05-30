<?php

namespace App\Console\Commands;

use App\Sync\Services\UpdateService;
use Illuminate\Console\Command;

class CheckUpdates extends Command
{
    protected $signature = 'update:check {--apply : Apply updates if available}';
    protected $description = 'Check for UI/code updates from the sync server';

    public function __construct(protected UpdateService $updateService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for updates...');

        $result = $this->updateService->checkForUpdates();

        if ($result['has_update'] ?? false) {
            $this->info("Update available: v{$result['server_version']}");
            if ($result['applied'] ?? false) {
                $this->info('Update applied successfully.');
            } else {
                $this->warn('Update check succeeded but apply failed. Check logs.');
            }
        } else {
            $reason = $result['reason'] ?? 'up_to_date';
            $this->line("No update needed. ($reason)");
        }

        return Command::SUCCESS;
    }
}
