<?php

namespace App\Sync\Services;

use Illuminate\Support\Facades\Config;

class SecurityService
{
    /**
     * Derives a unique encryption key for the local SQLite database
     * based on the device's hardware UUID.
     */
    public static function getDatabaseKey(): string
    {
        $deviceUuid = env('DEVICE_UUID', 'hms-default-key');
        return hash_hmac('sha256', $deviceUuid, config('app.key'));
    }

    /**
     * Dynamically configures the database connection for SQLCipher
     */
    public static function configureEncryptedDb(): void
    {
        if (env('APP_MODE') === 'local') {
            Config::set('database.connections.sqlite.password', self::getDatabaseKey());
        }
    }
}
