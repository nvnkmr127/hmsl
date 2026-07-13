<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupOfflineMode extends Command
{
    protected $signature = 'app:setup-offline';
    protected $description = 'Set up the application for offline-first mode using SQLite';

    public function handle()
    {
        $this->info('=== Starting Offline Mode Setup ===');

        $envPath = base_path('.env');
        $envOfflinePath = base_path('.env.offline');
        $envBackupPath = base_path('.env.backup');

        // 1. Copy .env.offline to .env (backup existing)
        if (!file_exists($envOfflinePath)) {
            $this->error('❌ Error: .env.offline file not found!');
            return 1;
        }

        if (file_exists($envPath)) {
            if (copy($envPath, $envBackupPath)) {
                $this->info('✔ Backed up existing .env to .env.backup');
            } else {
                $this->error('❌ Error: Failed to backup existing .env file!');
                return 1;
            }
        }

        if (copy($envOfflinePath, $envPath)) {
            $this->info('✔ Copied .env.offline to .env');
        } else {
            $this->error('❌ Error: Failed to copy .env.offline to .env!');
            return 1;
        }

        $php = PHP_BINARY;

        // 2. Run migrations
        $this->info('Running database migrations...');
        $output = [];
        $resultCode = 0;
        exec("\"$php\" artisan migrate --force", $output, $resultCode);
        if ($resultCode === 0) {
            $this->info('✔ Migrations completed successfully.');
        } else {
            $this->error('❌ Migrations failed:');
            $this->error(implode("\n", $output));
            return 1;
        }

        // 3. Run seeder
        $this->info('Seeding roles and permissions database...');
        $output = [];
        exec("\"$php\" artisan db:seed --class=RolePermissionSeeder --force", $output, $resultCode);
        if ($resultCode === 0) {
            $this->info('✔ Seeding completed successfully.');
        } else {
            $this->error('❌ Seeding failed:');
            $this->error(implode("\n", $output));
            return 1;
        }

        // 4. Create storage link
        $this->info('Creating storage symbolic link...');
        $output = [];
        exec("\"$php\" artisan storage:link", $output, $resultCode);
        if ($resultCode === 0 || str_contains(implode(' ', $output), 'already exists')) {
            $this->info('✔ Storage link configured successfully.');
        } else {
            $this->warn('⚠ Warning: Storage link issue: ' . implode("\n", $output));
        }

        // 5. Clear config & cache
        $this->info('Clearing configuration cache...');
        exec("\"$php\" artisan config:clear");
        $this->info('✔ Config cache cleared.');

        $this->info('Clearing application cache...');
        exec("\"$php\" artisan cache:clear");
        $this->info('✔ Application cache cleared.');

        // 6. Write sync_status.json
        $syncStatusPath = storage_path('app/sync_status.json');
        $dir = dirname($syncStatusPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $initialState = [
            'status' => 'offline',
            'last_sync' => null,
            'pending_changes' => 0,
        ];
        if (file_put_contents($syncStatusPath, json_encode($initialState, JSON_PRETTY_PRINT))) {
            $this->info('✔ Initialized storage/app/sync_status.json');
        } else {
            $this->error('❌ Failed to write storage/app/sync_status.json');
            return 1;
        }

        $this->info('=== Offline Mode Setup Complete! ===');
        return 0;
    }
}
