<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh the OTIF backlog from Epicor on a schedule (only fetches when
// OTIF_SOURCE=epicor; otherwise it's a no-op). Requires `php artisan schedule:work`
// or a system cron entry running `schedule:run` every minute.
Schedule::command('otif:fetch')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
