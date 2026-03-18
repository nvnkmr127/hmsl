<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get a setting value by key.
     */
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Set a setting value by key.
     */
    public function set(string $key, $value, string $group = 'system')
    {
        return Setting::set($key, $value, $group);
    }

    /**
     * Get all settings from a specific group.
     */
    public function getGroup(string $group): array
    {
        return Setting::getGroup($group);
    }

    /**
     * Update multiple settings in a group.
     */
    public function updateGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value, $group);
        }
        
        Cache::forget("settings.group.{$group}");
    }
}
