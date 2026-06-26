<?php

return [
    'backup_path' => storage_path('app/backups'),
    'temp_path' => storage_path('app/backup-temp'),
    'max_storage_mb' => 5000,
    'retention' => [
        'days_all' => 7,
        'days_daily' => 16,
        'weeks_weekly' => 8,
        'months_monthly' => 4,
        'years_yearly' => 2,
    ],
    'code_backup_includes' => ['app', 'config', 'database/migrations', 'routes', 'resources/views', 'public'],
    'code_backup_excludes' => ['vendor', 'node_modules', '.git', '.env', 'storage/app/backups'],
];
