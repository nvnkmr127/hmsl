<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FirstRunSetup extends Command
{
    protected $signature = 'app:first-run';
    protected $description = 'Set up HMS database and default admin account on first application launch';

    public function handle()
    {
        chdir(base_path());
        $this->info('=== Running HMS First-Run Setup ===');


        // Ensure DB_DATABASE stays as a portable relative path ("database/database.sqlite").
        // Laravel resolves this relative to base_path(), so it works on any machine
        // regardless of installation directory — no absolute path rewriting needed.
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            // If someone has set an absolute path, normalise it back to relative
            if (preg_match('/^DB_DATABASE=(?!database\/database\.sqlite)/m', $envContent)) {
                $envContent = preg_replace(
                    '/^DB_DATABASE=.*/m',
                    'DB_DATABASE=database/database.sqlite',
                    $envContent
                );
                file_put_contents($envFile, $envContent);
                $this->info('✔ Normalised DB_DATABASE to relative path.');
            }
        }

        // Ensure all required storage subdirectories exist
        foreach (['app', 'app/public', 'framework/cache', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
            $path = storage_path($dir);
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        $dbPath = config('database.connections.sqlite.database');

        // Touch database file if it does not exist
        if (!file_exists($dbPath)) {
            $this->info('✔ Creating SQLite database file...');
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            touch($dbPath);
        }

        $php = PHP_BINARY;

        // Run migrations
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

        // Run seeders
        $this->info('Seeding database with roles and permissions...');
        $output = [];
        exec("\"$php\" artisan db:seed --class=RolePermissionSeeder --force", $output, $resultCode);
        if ($resultCode === 0) {
            $this->info('✔ Seeding completed successfully.');
        } else {
            $this->error('❌ Seeding failed:');
            $this->error(implode("\n", $output));
            return 1;
        }

        // Run admin users seeder
        $this->info('Seeding database with default user accounts...');
        $output = [];
        exec("\"$php\" artisan db:seed --class=AdminUserSeeder --force", $output, $resultCode);
        if ($resultCode === 0) {
            $this->info('✔ Seeding user accounts completed successfully.');
        } else {
            $this->error('❌ Seeding user accounts failed:');
            $this->error(implode("\n", $output));
            return 1;
        }

        // Create sync_status.json
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

        // Create .first_run_complete marker file
        $markerPath = base_path('.first_run_complete');
        if (file_put_contents($markerPath, now()->toDateTimeString())) {
            $this->info('✔ Created first-run setup complete marker.');
        } else {
            $this->error('❌ Failed to write .first_run_complete marker file');
            return 1;
        }

        $this->info('=== Setup complete! Log in at: http://127.0.0.1:8000/login ===');
        return 0;
    }
}
