<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Illuminate\Support\Facades\DB;

class BackupCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up old backup files';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting backup cleanup...');

        try {
            $days = $this->option('days');
            
            if (!$days) {
                // Fetch from settings or default to 30
                $days = (int) DB::table('settings')->where('key', 'backup_retention_days')->value('value') ?: 30;
            } else {
                $days = (int) $days;
            }

            $deletedCount = $backupService->cleanupOldBackups($days);

            $this->info("Cleanup successful. Deleted {$deletedCount} old backup file(s) older than {$days} days.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
