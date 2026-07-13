<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BackupMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:monitor {--notify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitors the health of the backup system';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting backup monitor checks...');
        $issues = [];
        
        $backupPath = storage_path('app/backups');
        
        // Check 1: Write permissions
        if (!File::isWritable($backupPath)) {
            $issues[] = "Backup directory ({$backupPath}) is not writable.";
            $this->error("Backup directory is not writable.");
        } else {
            $this->info("✔ Backup directory is writable.");
        }

        // Check 2: Disk space
        $freeSpace = disk_free_space($backupPath);
        if ($freeSpace !== false) {
            $freeSpaceMB = number_format($freeSpace / 1048576, 2);
            if ($freeSpace < 524288000) { // < 500MB
                $issues[] = "Low disk space in backup directory: {$freeSpaceMB} MB remaining.";
                $this->error("Low disk space: {$freeSpaceMB} MB");
            } else {
                $this->info("✔ Disk space is adequate ({$freeSpaceMB} MB free).");
            }
        }

        $localBackups = $backupService->getLocalBackups();
        
        // Check 3: Database backup in last 25 hours
        $hasRecentDbBackup = false;
        foreach ($localBackups as $backup) {
            if ($backup['type'] === 'database' && (time() - $backup['date']) <= (25 * 3600)) {
                $hasRecentDbBackup = true;
                break;
            }
        }
        
        if (!$hasRecentDbBackup) {
            $issues[] = "No database backup found in the last 25 hours.";
            $this->error("No recent database backup found.");
        } else {
            $this->info("✔ Recent database backup found.");
        }

        // Check 4: Code backup in last 4 days
        $hasRecentCodeBackup = false;
        foreach ($localBackups as $backup) {
            if ($backup['type'] === 'code' && (time() - $backup['date']) <= (4 * 24 * 3600)) {
                $hasRecentCodeBackup = true;
                break;
            }
        }

        if (!$hasRecentCodeBackup) {
            $issues[] = "No code backup found in the last 4 days.";
            $this->error("No recent code backup found.");
        } else {
            $this->info("✔ Recent code backup found.");
        }

        // Notify if there are issues and --notify flag is present
        if (count($issues) > 0) {
            if ($this->option('notify')) {
                $notificationEmail = DB::table('settings')->where('key', 'notification_email')->value('value');
                
                if ($notificationEmail) {
                    $this->info("Sending alert to {$notificationEmail}...");
                    
                    try {
                        // Using raw mail here, you can switch to a Mailable if needed
                        Mail::raw("The following issues were detected by the Backup Monitor:\n\n" . implode("\n", $issues), function ($message) use ($notificationEmail) {
                            $message->to($notificationEmail)
                                    ->subject('Backup System Alert');
                        });
                        $this->info("Alert sent.");
                    } catch (\Exception $e) {
                        $this->error("Failed to send alert email: " . $e->getMessage());
                    }
                } else {
                    $this->warn("Notification email not set in settings. Skipping email alert.");
                }
            }
            
            return Command::FAILURE;
        }

        $this->info('All checks passed.');
        return Command::SUCCESS;
    }
}
