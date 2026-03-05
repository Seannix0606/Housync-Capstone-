<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:generate-recurring --type=rent')
    ->monthlyOn(1, '06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/recurring-billing.log'));

Schedule::command('lease:check-expiry --days=30')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/lease-expiry.log'));
