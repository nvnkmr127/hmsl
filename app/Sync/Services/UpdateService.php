<?php

namespace App\Sync\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class UpdateService
{
    public function checkForUpdates(): array
    {
        $serverUrl = config('sync.server_url');
        $token = config('sync.token');

        if (!$serverUrl || !$token) {
            return ['has_update' => false, 'reason' => 'not_configured'];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get($serverUrl . '/api/v1/update/check', [
                    'current_version' => env('APP_VERSION', '1.0.0'),
                ]);

            if (!$response->successful()) {
                return ['has_update' => false, 'reason' => 'server_error'];
            }

            $data = $response->json();

            if ($data['has_update'] ?? false) {
                return array_merge($data, ['applied' => $this->applyUpdates($serverUrl, $token)]);
            }

            return ['has_update' => false, 'current_version' => $data['server_version'] ?? null];

        } catch (\Exception $e) {
            Log::warning('UpdateService: ' . $e->getMessage());
            return ['has_update' => false, 'reason' => 'connection_error'];
        }
    }

    protected function applyUpdates(string $serverUrl, string $token): bool
    {
        try {
            // 1. Get manifest from server
            $manifestResponse = Http::withToken($token)
                ->timeout(30)
                ->get($serverUrl . '/api/v1/update/manifest');

            if (!$manifestResponse->successful()) {
                Log::warning('UpdateService: Failed to fetch manifest');
                return false;
            }

            $manifest = $manifestResponse->json();
            $serverFiles = $manifest['files'] ?? [];
            $serverVersion = $manifest['version'] ?? null;

            // 2. Compare to local files, download only changed ones
            $downloaded = 0;
            foreach ($serverFiles as $relativePath => $serverHash) {
                $localPath = base_path($relativePath);
                $localHash = file_exists($localPath) ? md5_file($localPath) : null;

                if ($localHash === $serverHash) {
                    continue; // Already up to date
                }

                // Download the changed file
                $fileResponse = Http::withToken($token)
                    ->timeout(30)
                    ->get($serverUrl . '/api/v1/update/download', ['path' => $relativePath]);

                if ($fileResponse->successful()) {
                    $dir = dirname($localPath);
                    if (!File::exists($dir)) {
                        File::makeDirectory($dir, 0755, true);
                    }
                    File::put($localPath, $fileResponse->body());
                    $downloaded++;
                    Log::info("UpdateService: Updated $relativePath");
                }
            }

            // 3. Run any new migrations
            Artisan::call('migrate', ['--force' => true]);

            // 4. Clear compiled views and caches
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            // 5. Update local version
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);
            if ($serverVersion) {
                $envContent = preg_replace('/^APP_VERSION=.*/m', "APP_VERSION={$serverVersion}", $envContent);
                if (!str_contains($envContent, 'APP_VERSION=')) {
                    $envContent .= "\nAPP_VERSION={$serverVersion}";
                }
                file_put_contents($envPath, $envContent);
            }

            Log::info("UpdateService: Applied update v{$serverVersion}, {$downloaded} files changed");
            return true;

        } catch (\Exception $e) {
            Log::error('UpdateService applyUpdates: ' . $e->getMessage());
            return false;
        }
    }
}
