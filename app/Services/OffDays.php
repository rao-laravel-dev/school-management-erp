<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use Carbon\Carbon;

// Off din (holiday) ka shared logic: staff attendance aur payroll yahin se lete hain
class OffDays
{
    /**
     * [start, end] ke andar ki saari off-day dates: ['Y-m-d' => true].
     * Rule: calendar entry status = 1 aur event type is_off_day = 1. Academic year filter nahi
     * (har entry apni date par maani jati hai, is liye purane mahine bhi sahi). Date-only compare,
     * multi-day entries expand aur [start, end] se clip. Ek hi query.
     */
    public static function dates(Carbon $start, Carbon $end): array
    {
        $s = $start->toDateString();
        $e = $end->toDateString();

        $events = AcademicCalendar::active()
            ->whereHas('eventType', fn ($q) => $q->where('is_off_day', true))
            ->whereDate('start_date', '<=', $e)
            ->whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [$s])
            ->get();

        $dates = [];
        foreach ($events as $event) {
            $from = max($event->start_date->toDateString(), $s);
            $to   = min(($event->end_date ?: $event->start_date)->toDateString(), $e);
            $last = Carbon::parse($to);

            for ($d = Carbon::parse($from); $d->lte($last); $d->addDay()) {
                $dates[$d->toDateString()] = true;
            }
        }

        return $dates;
    }

    /**
     * Payroll ke liye weekly off din (English naam). Wo academic year jis ki start_date/end_date
     * $date ko cover kare (active year nahi). Koi year cover na kare to FALLBACK ['Sunday'].
     */
    public static function weeklyOffDays(Carbon $date): array
    {
        $year = AcademicYear::whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->orderByDesc('start_date')
            ->first();

        if (!$year) {
            return ['Sunday'];
        }

        // Sab untick = khali array (koi weekly off nahi); NULL (purana record) = Sunday
        return is_array($year->weekly_off_days) ? $year->weekly_off_days : ['Sunday'];
    }
}
