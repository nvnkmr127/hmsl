<?php

namespace App\Sync\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class UpdateService
{
    public function checkForUpdates(): bool
    {
        $currentVersion = env('APP_VERSION', '1.0.0');
        
        $response = Http::get(env('UPDATE_SERVER_URL') . '/api/v1/update/check', [
            'current_version' => $currentVersion,
            'os' => 'windows'
        ]);

        if ($response->successful() && $response->json('has_update')) {
            return $this->applyUpdates($response->json('update_data'));
        }

        return false;
    }

    protected function applyUpdates(array $updateData): bool
    {
        // 1. Download Changed Files
        foreach ($updateData['files'] as $file) {
            $content = Http::get($file['url'])->body();
            File::put(base_path($file['path']), $content);
        }

        // 2. Run Migrations on SQLite
        if ($updateData['has_migrations']) {
            Artisan::call('migrate', ['--force' => true]);
        }

        // 3. Clear Cache
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        return true;
    }
}
