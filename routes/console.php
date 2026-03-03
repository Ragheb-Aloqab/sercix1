<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fetch vehicle locations from tracking APIs every 5 minutes
Schedule::command('tracking:fetch-locations')->everyFiveMinutes();

// Admin alerts: stuck orders, inactive companies, failed jobs (daily at 8:00)
Schedule::job(new \App\Jobs\NotifyAdminAlertsJob)->dailyAt('08:00');

// Daily admin summary report
Schedule::command('admin:daily-summary')->dailyAt('09:00');

// Vehicle inspections: schedule monthly inspections daily (creates pending when due)
Schedule::command('inspections:schedule')->dailyAt('06:00');

// Monthly mileage: snapshot at month start (1st 00:05), update current month daily (01:00)
Schedule::command('mileage:capture-monthly')->monthlyOn(1, '00:05');
Schedule::command('mileage:capture-monthly')->dailyAt('01:00');
