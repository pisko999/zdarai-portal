<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// withoutOverlapping: mutex přes Redis cache — brání překryvu dvou běhů téhož příkazu.
// onOneServer: pojistka proti souběhu schedule:run z více zdrojů (viz TF #1348 — duplicitní cron mtg + www-data).
Schedule::command('reminders:send')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();
