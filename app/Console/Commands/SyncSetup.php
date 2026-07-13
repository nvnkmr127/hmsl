<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncSetup extends Command
{
    protected $signature = 'sync:setup {--url= : The production server URL} {--name= : The name for this device}';
    protected $description = 'Configure this local installation to sync with a production server';

    public function handle()
    {
        $this->info('Starting Sync Setup...');

        $url = $this->option('url') ?: $this->ask('What is the Production Server URL? (e.g., https://hms-prod.com)');
        $name = $this->option('name') ?: $this->ask('What is the name for this device? (e.g., Pharmacy_Desk)');

        if (!$url || !$name) {
            $this->error('URL and Name are required.');
            return 1;
        }

        $url = rtrim($url, '/');
        $uuid = (string) Str::uuid();

        $this->info("Registering device '$name' with $url...");

        try {
            $response = Http::post($url . '/api/v1/sync/register', [
                'device_uuid' => $uuid,
                'name' => $name,
            ]);

            if ($response->successful()) {
                $token = $response->json('token');
                
                $this->updateDotEnv([
                    'SYNC_SERVER_URL' => $url,
                    'SYNC_TOKEN' => $token,
                    'DEVICE_ID' => $name,
                ]);

                $this->info('Registration Successful!');
                $this->info('Local .env file has been updated.');
                $this->comment('You can now run "php artisan sync:perform" to start syncing.');
            } else {
                $this->error('Registration failed: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Could not connect to the server: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function updateDotEnv(array $data)
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            return;
        }

        $content = File::get($path);

        foreach ($data as $key => $value) {
            if (str_contains($content, "{$key}=")) {
                $content = preg_replace("/{$key}=.*/", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        File::put($path, $content);
    }
}
