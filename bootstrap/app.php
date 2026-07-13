<?php

error_reporting(E_ALL & ~E_DEPRECATED);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// When running inside the Tauri desktop app, the install directory
// (C:\Program Files\...) is read-only. Tauri passes writable AppData
// paths via environment variables so Laravel can write files.
$storagePath    = getenv('STORAGE_PATH') ?: null;
$bootstrapCache = getenv('BOOTSTRAP_CACHE_PATH') ?: null;
$envFile        = getenv('APP_ENV_FILE') ?: null;

$app = Application::configure(basePath: dirname(__DIR__));

// Override storage path if provided
if ($storagePath && is_dir($storagePath)) {
    $app->useStoragePath($storagePath);
}

// Override bootstrap cache path if provided
if ($bootstrapCache) {
    if (!is_dir($bootstrapCache)) {
        @mkdir($bootstrapCache, 0755, true);
    }
    $app->useBootstrapPath($bootstrapCache);
}

// Override .env file location: load from AppData path directly using Dotenv
if ($envFile && file_exists($envFile)) {
    // Manually load the .env from the writable AppData location
    // before Laravel's own env loading so values are available immediately
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV)) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

return $app
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'hospital_owner' => \App\Http\Middleware\EnsureHospitalOwner::class,
            'sync.device' => \App\Sync\Middleware\ValidateSyncDevice::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('sync:perform')->everyMinute()->withoutOverlapping()->runInBackground();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
