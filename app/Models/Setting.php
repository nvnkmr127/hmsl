<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value, $group = 'system')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("setting.{$key}");
        Cache::forget("settings.group.{$group}");
        
        return $setting;
    }

    public static function getGroup(string $group)
    {
        return Cache::rememberForever("settings.group.{$group}", function () use ($group) {
            return self::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }
}
