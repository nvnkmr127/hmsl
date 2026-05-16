<?php

namespace App\Sync\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationRunner
{
    /**
     * Executes pending migrations on the local database.
     */
    public function runLocalMigrations(): bool
    {
        try {
            // 1. Snapshot the current database (Backup)
            $this->createBackup();

            // 2. Run migrations
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--database' => 'sqlite'
            ]);

            if ($exitCode === 0) {
                Log::info('Local migrations applied successfully.');
                return true;
            }

            $this->restoreBackup();
            return false;

        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            $this->restoreBackup();
            return false;
        }
    }

    protected function createBackup(): void
    {
        $dbPath = database_path('database.sqlite');
        copy($dbPath, $dbPath . '.bak');
    }

    protected function restoreBackup(): void
    {
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath . '.bak')) {
            rename($dbPath . '.bak', $dbPath);
        }
    }
}
