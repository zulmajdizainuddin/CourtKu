<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expire unpaid holds and complete finished bookings (ADR-022). Runs on a server with a scheduler
// (`php artisan schedule:work` locally). On Render's free plan a cron service calls POST /api/tick instead.
Schedule::command('courtku:tick')->everyMinute()->withoutOverlapping();
