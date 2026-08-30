<?php

use App\Models\Attendance;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Attendance::where('status', 1)
        ->whereNull('time_out')
        ->update(['time_out' => '14:00:00']);
})->dailyAt('14:00');
