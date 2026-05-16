<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Illuminate\Support\Facades\Schedule::command('hms:cron-run report-summary')->dailyAt('22:00');
Illuminate\Support\Facades\Schedule::command('hms:cron-run prune-webhooks')->daily();
Illuminate\Support\Facades\Schedule::command('hms:cron-run retry-outbox')->everyThirtyMinutes();
Illuminate\Support\Facades\Schedule::command('hms:cron-run queue-worker')->everyMinute();
Illuminate\Support\Facades\Schedule::job(new \App\Sync\Jobs\PerformBackgroundSync)->everyMinute()->withoutOverlapping();
Illuminate\Support\Facades\Schedule::command('telescope:prune --hours=48')->daily();
