<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sync\Models\SyncDevice;
use Illuminate\Support\Str;

class RegisterSyncDevice extends Command
{
    protected $signature = 'sync:register-device {name}';
    protected $description = 'Register a new local device and generate a sync token';

    public function handle()
    {
        $name = $this->argument('name');
        $uuid = (string) Str::uuid();

        $device = SyncDevice::create([
            'device_uuid' => $uuid,
            'name' => $name,
            'status' => 'active',
        ]);

        $token = $device->createToken('sync-token')->plainTextToken;

        $this->info("Device Registered Successfully!");
        $this->info("Name: $name");
        $this->info("Device UUID: $uuid");
        $this->line("");
        $this->warn("SYNC_TOKEN: $token");
        $this->line("");
        $this->comment("Use this token in your local node's .env file.");
    }
}
