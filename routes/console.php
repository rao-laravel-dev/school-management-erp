<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolTiming;
use App\Notifications\AttendanceSummaryNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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

// Scan attendance summary: active School Timing ke attendance_summary_time par, din mein ek baar (har class-section + staff).
// Auto time-out ke baad register hai; yahan parent alert nahi, sirf admin/superadmin summary
Schedule::call(function () {
    $timing = SchoolTiming::active();
    if (!$timing || !$timing->attendance_summary_time) {
        return; // time set nahi = scan summary nahi
    }
    if (now()->format('H:i:s') < $timing->attendance_summary_time) {
        return; // summary ka waqt abhi nahi aaya
    }
    $today = today()->toDateString();
    // Aaj koi scan hi nahi hua (off day / scan use nahi hua) = kuch nahi bhejna
    if (!Attendance::whereDate('attendance_date', $today)->where('marked_via', 'device')->exists()) {
        return;
    }
    // Din mein ek hi run
    if (!Cache::add("att_summary:scan_run:{$today}", true, now()->endOfDay())) {
        return;
    }

    $source = 'Scan summary at ' . now()->format('g:i A');
    $recipients = AttendanceSummaryNotification::recipients();

    // Active students wali har class-section; Holiday wali class skip
    $classSections = Enrollment::whereHas('student')
        ->where(fn($q) => AcademicYear::whereClassSession($q))
        ->where('enroll_status', 1)
        ->select('class_id', 'section_id')
        ->distinct()
        ->get();

    foreach ($classSections as $cs) {
        $isHoliday = Attendance::whereDate('attendance_date', $today)
            ->where('class_id', $cs->class_id)
            ->where('section_id', $cs->section_id)
            ->where('status', Attendance::STATUS_LEAVE)
            ->whereRaw("LOWER(TRIM(COALESCE(remarks, ''))) = 'holiday'")
            ->exists();
        if ($isHoliday) {
            continue;
        }
        // Manual save pehle summary bhej chuka ho to key maujood hai: dobara nahi
        if (!Cache::add("att_summary:class:{$cs->class_id}:{$cs->section_id}:{$today}", true, now()->endOfDay())) {
            continue;
        }
        Notification::send($recipients, AttendanceSummaryNotification::forClass($cs->class_id, $cs->section_id, $today, $source));
    }

    if (Cache::add("att_summary:staff:{$today}", true, now()->endOfDay())) {
        Notification::send($recipients, AttendanceSummaryNotification::forStaff($today, $source));
    }
})->everyMinute()->name('attendance-scan-summary')->withoutOverlapping();

// Queue worker: web push jobs ek minute ke andar nikal jate hain (alag server cron nahi chahiye)
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();

// Auto monthly fees + salary slips (billing_settings ke auto switches); dobara chalane se kuch double nahi banta
Schedule::command('billing:generate-monthly')->dailyAt('01:00')->withoutOverlapping();
