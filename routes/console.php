<?php

use App\Jobs\System\RunBackupJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RunBackupJob(type: 'database', destination: 'local'))
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer();
