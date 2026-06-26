<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--storage=local : local or gdrive} {--type=daily}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting database backup...');

        try {
            $type = $this->option('type');
            $storage = $this->option('storage');

            $filePath = $backupService->createDatabaseBackup($type);
            $fileName = basename($filePath);
            $size = number_format(filesize($filePath) / 1048576, 2);

            $this->info("Backup created locally: {$fileName} ({$size} MB)");

            if ($storage === 'gdrive') {
                $this->info('Uploading to Google Drive...');
                $fileId = $backupService->uploadToGoogleDrive($filePath, $fileName);
                $this->info("Successfully uploaded to Google Drive. File ID: {$fileId}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
