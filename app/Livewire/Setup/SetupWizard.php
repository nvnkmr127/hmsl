<?php

namespace App\Livewire\Setup;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;

class SetupWizard extends Component
{
    public int $step = 1;
    public string $serverUrl = '';
    public string $deviceName = '';
    public string $token = '';
    public string $deviceUuid = '';
    public bool $syncing = false;
    public bool $syncDone = false;
    public string $error = '';
    public bool $connectionOk = false;

    public function mount(): void
    {
        $this->deviceUuid = (string) Str::uuid();
    }

    public function testConnection(): void
    {
        $this->error = '';
        $this->connectionOk = false;

        if (empty(trim($this->serverUrl))) {
            $this->error = 'Please enter a server URL.';
            return;
        }

        try {
            $url = rtrim(trim($this->serverUrl), '/');
            $response = Http::timeout(5)->get("{$url}/api/v1/update/check?current_version=0.0.0");

            if ($response->successful()) {
                $this->connectionOk = true;
                $this->serverUrl = $url;
                $this->step = 2;
            } else {
                $this->error = "Server responded with HTTP {$response->status()}. Please check the URL.";
            }
        } catch (\Exception $e) {
            $this->error = 'Could not connect: ' . $e->getMessage();
        }
    }

    public function registerDevice(): void
    {
        $this->error = '';

        if (empty(trim($this->deviceName))) {
            $this->error = 'Please enter a device name.';
            return;
        }

        try {
            $url = rtrim(trim($this->serverUrl), '/');
            $response = Http::timeout(10)->post("{$url}/api/v1/sync/register", [
                'device_uuid' => $this->deviceUuid,
                'name'        => $this->deviceName,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'] ?? ($data['data']['token'] ?? '');

                $this->writeEnv($this->serverUrl, $this->token, $this->deviceUuid);
                $this->step = 3;
            } else {
                $this->error = "Registration failed (HTTP {$response->status()}): " . ($response->json('message') ?? 'Unknown error.');
            }
        } catch (\Exception $e) {
            $this->error = 'Registration error: ' . $e->getMessage();
        }
    }

    public function runFirstSync(): void
    {
        $this->error = '';
        $this->syncing = true;
        $this->syncDone = false;

        try {
            app(\App\Sync\Services\SyncEngine::class)->performSync();
            app(\App\Sync\Services\UpdateService::class)->checkForUpdates();
        } catch (\Exception $e) {
            // Non-fatal — log but continue so the user can still proceed.
            logger()->warning('First-sync warning: ' . $e->getMessage());
        }

        $this->syncing = false;
        $this->syncDone = true;
        $this->step = 4;
    }

    private function writeEnv(string $serverUrl, string $token, string $deviceUuid): void
    {
        $envPath = base_path('.env');
        $env = file_exists($envPath) ? file_get_contents($envPath) : '';

        $replacements = [
            'SYNC_SERVER_URL'   => $serverUrl,
            'UPDATE_SERVER_URL' => $serverUrl,
            'SYNC_TOKEN'        => $token,
            'DEVICE_ID'         => $deviceUuid,
        ];

        foreach ($replacements as $key => $value) {
            $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$escapedValue}";

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $replacement, $env);
            } else {
                $env .= PHP_EOL . $replacement;
            }
        }

        file_put_contents($envPath, $env);

        // Generate APP_KEY if missing
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        // Reload env values in the running process
        foreach ($replacements as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public function render()
    {
        return view('livewire.setup.setup-wizard')
            ->layout('layouts.setup');
    }
}
