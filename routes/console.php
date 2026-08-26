<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:send')->everyFifteenMinutes();
Schedule::command('demo:cleanup')->hourly();
// Ticks every hour; the command itself acts only on the hour the admin configured.
Schedule::command('greetings:send')->hourly();
Schedule::command('promo:release-commissions')->hourly();
