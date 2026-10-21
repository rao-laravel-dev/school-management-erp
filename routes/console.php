<?php

use App\Models\Attendance;
use App\Models\SchoolTiming;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto time_out: sirf aaj ke Present/Late rows, active School Timing ke end_time par (hardcoded time nahi)
Schedule::call(function () {
    $timing = SchoolTiming::active();
    if (!$timing) {
        // din mein ek hi warning (har 15 min log na bhare)
        if (Cache::add('auto_time_out_warned_' . today()->toDateString(), true, now()->endOfDay())) {
            Log::warning('Auto time_out skipped: no active School Timing set.');
        }
        return;
    }
    if (now()->format('H:i:s') < $timing->end_time) {
        return; // chutti abhi nahi hui
    }
    $count = Attendance::whereDate('attendance_date', today())
        ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])
        ->whereNull('time_out')
        ->update(['time_out' => $timing->end_time]);
    if ($count > 0) {
        Log::info("Auto time_out: {$count} attendance rows set to {$timing->end_time} for " . today()->toDateString());
    }
})->everyFifteenMinutes()->name('attendance-auto-time-out')->withoutOverlapping();

// Auto monthly fees + salary slips (billing_settings ke auto switches); dobara chalane se kuch double nahi banta
Schedule::command('billing:generate-monthly')->dailyAt('01:00')->withoutOverlapping();
