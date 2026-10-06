<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use Carbon\Carbon;

class TimetableDayStatus
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * Ek hafte ka din-wise status (2 queries, N+1 nahi).
     * Order: off-type calendar event > periods > weekly off > not scheduled.
     *
     * @param  string|null $week      koi bhi Y-m-d (default aaj)
     * @param  array       $periodDays din ke naam jin par periods saved hain (e.g. ['Monday', 'Sunday'])
     */
    public static function forWeek(?string $week, array $periodDays): array
    {
        $monday = Carbon::parse($week ?: now()->toDateString())->startOfWeek(Carbon::MONDAY);
        $sunday = $monday->copy()->addDays(6);

        $year = AcademicYear::where('is_current', 1)->first();
        $weeklyOff = $year ? ($year->weekly_off_days ?: []) : [];

        // Active year ki off-type calendar entries jo is hafte se overlap karti hain (date-only compare)
        $events = collect();
        if ($year) {
            $events = AcademicCalendar::active()
                ->forYear($year->id)
                ->whereHas('eventType', fn ($q) => $q->where('is_off_day', true))
                ->whereDate('start_date', '<=', $sunday->toDateString())
                ->whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [$monday->toDateString()])
                ->get();
        }

        $dayStatus = [];
        foreach (self::DAYS as $i => $day) {
            $date = $monday->copy()->addDays($i)->toDateString();

            // 1) Off-type calendar event periods par bhi bhaari
            $event = $events->first(function ($e) use ($date) {
                $start = $e->start_date->toDateString();
                $end = ($e->end_date ?: $e->start_date)->toDateString();
                return $start <= $date && $end >= $date;
            });

            if ($event) {
                $status = 'not_scheduled';
                $label = $event->title;
            } elseif (in_array($day, $periodDays, true)) {
                // 2) Periods hon to hamesha dikhao (weekly off din par bhi)
                $status = 'periods';
                $label = null;
            } elseif (in_array($day, $weeklyOff, true)) {
                $status = 'weekly_off';
                $label = null;
            } else {
                $status = 'not_scheduled';
                $label = null;
            }

            $dayStatus[$day] = [
                'date' => $date,
                'date_label' => Carbon::parse($date)->format('d M'),
                'status' => $status,
                'label' => $label,
            ];
        }

        return [
            'day_status' => $dayStatus,
            'week' => [
                'start' => $monday->toDateString(),
                'end' => $sunday->toDateString(),
                'prev' => $monday->copy()->subWeek()->toDateString(),
                'next' => $monday->copy()->addWeek()->toDateString(),
                'label' => $monday->format('d M') . ' - ' . $sunday->format('d M Y'),
            ],
        ];
    }
}
