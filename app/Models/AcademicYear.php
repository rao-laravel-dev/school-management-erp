<?php

namespace App\Models;

use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    // 🛠️ FIX: Yahan 'start_date' aur 'end_date' ko add kar diya hai
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'status',
        'weekly_off_days',
        'class_date_overrides'
    ];

    // Seeder/tinker se bane records NULL na hon: default weekly off Sunday
    protected $attributes = [
        'weekly_off_days' => '["Sunday"]',
    ];

    // 🛠️ FIX: Dates ko proper Carbon instances mein cast karne ke liye array add kiya
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'weekly_off_days' => 'array',
        // [{class_ids, start_date, end_date}] ya NULL (sab classes session ki dates par)
        'class_date_overrides' => 'array',
    ];

    // Name Accessor - Output letay waqt hamesha UpperCase aur clean format mein mile
    public function getNameAttribute($value)
    {
        return $value ? strtoupper(trim($value)) : null;
    }

    // Name Mutator - Database mein save karte waqt hamesha UpperCase save kare (e.g., 2026-2027)
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value ? strtoupper(trim($value)) : null;
    }

    /**
     * 🔗 Relationship: Ek Academic Year me bohot saari student enrollments ho sakti hain.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'academic_year_id', 'id');
    }

    public function academicCalendars()
    {
        return $this->hasMany(AcademicCalendar::class);
    }

    // AcademicYear.php mein add karein
    public static function getActiveSessionId()
    {
        // Caching use karein taake baar baar DB hit na ho
        return cache()->remember('active_session_id', 3600, function () {
            return self::where('is_current', 1)->value('id');
        });
    }

    /**
     * Class ki session dates: class kisi override group mein ho to us group ki dates, warna session ki apni.
     * Return [Carbon|null start, Carbon|null end]. Overrides NULL = hamesha session ki dates (purana behaviour).
     */
    public function datesForClass(?int $classId): array
    {
        if ($classId) {
            foreach ((array) $this->class_date_overrides as $row) {
                $ids = array_map('intval', (array) (isset($row['class_ids']) ? $row['class_ids'] : []));
                if (in_array($classId, $ids, true) && !empty($row['start_date']) && !empty($row['end_date'])) {
                    return [Carbon::parse($row['start_date'])->startOfDay(), Carbon::parse($row['end_date'])->startOfDay()];
                }
            }
        }

        return [$this->start_date, $this->end_date];
    }

    // Per-request cache: gap context ek request mein ek hi baar nikalta hai
    protected static $gapContext = null;

    /**
     * Switch-over gap: naya session current hai magar kuch classes (override group) ki nayi dates abhi shuru nahi hui
     * aur pichle session mein un ki dates aaj ko cover karti hain. Un classes ke students abhi pichle session mein hain.
     * Return ['current' => id|null, 'prev' => id|null, 'classIds' => [gap class ids]]. Overrides NULL = classIds khali.
     */
    public static function gapContext(): array
    {
        if (static::$gapContext !== null) {
            return static::$gapContext;
        }

        $ctx = ['current' => self::getActiveSessionId(), 'prev' => null, 'classIds' => []];
        $current = $ctx['current'] ? self::find($ctx['current']) : null;

        if ($current && $current->start_date && !empty($current->class_date_overrides)) {
            $today = now()->startOfDay();
            $prev = self::where('id', '!=', $current->id)
                ->whereDate('start_date', '<', $current->start_date->toDateString())
                ->orderByDesc('start_date')
                ->first();

            if ($prev) {
                foreach ($current->class_date_overrides as $row) {
                    if (empty($row['start_date']) || Carbon::parse($row['start_date'])->startOfDay()->lte($today)) {
                        continue; // is group ki nayi dates shuru ho chuki hain: gap nahi
                    }
                    foreach ((array) (isset($row['class_ids']) ? $row['class_ids'] : []) as $classId) {
                        $prevEnd = $prev->datesForClass((int) $classId)[1];
                        if ($prevEnd && $prevEnd->startOfDay()->gte($today)) {
                            $ctx['classIds'][] = (int) $classId;
                        }
                    }
                }
                if ($ctx['classIds']) {
                    $ctx['prev'] = $prev->id;
                }
            }
        }

        return static::$gapContext = $ctx;
    }

    /**
     * Strict: class ki apni session ki rows. Gap class = pichla session, baqi = current session.
     * Gap na ho to bilkul purana `where(year, current)`.
     */
    public static function whereClassSession($query, string $yearCol = 'academic_year_id', string $classCol = 'class_id')
    {
        $ctx = self::gapContext();

        if (!$ctx['classIds']) {
            return $query->where($yearCol, $ctx['current']);
        }

        return $query->where(function ($w) use ($ctx, $yearCol, $classCol) {
            $w->where(fn($a) => $a->where($yearCol, $ctx['current'])->whereNotIn($classCol, $ctx['classIds']))
                ->orWhere(fn($b) => $b->where($yearCol, $ctx['prev'])->whereIn($classCol, $ctx['classIds']));
        });
    }

    /**
     * Union (lists): current session ki rows + gap classes ki pichle session ki Active rows.
     * Gap na ho to bilkul purana `where(year, current)`.
     */
    public static function whereCurrentOrGap($query, string $yearCol = 'academic_year_id', string $classCol = 'class_id', string $statusCol = 'enroll_status')
    {
        $ctx = self::gapContext();

        if (!$ctx['classIds']) {
            return $query->where($yearCol, $ctx['current']);
        }

        return $query->where(function ($w) use ($ctx, $yearCol, $classCol, $statusCol) {
            $w->where($yearCol, $ctx['current'])
                ->orWhere(fn($b) => $b->where($yearCol, $ctx['prev'])->whereIn($classCol, $ctx['classIds'])->where($statusCol, 1));
        });
    }

    // Ek enrollment row (year + class) abhi class ke hisab se sahi session mein hai ya nahi (whereClassSession ka single-row version)
    public static function isClassSession($yearId, $classId): bool
    {
        $ctx = self::gapContext();

        if ($ctx['classIds'] && in_array((int) $classId, $ctx['classIds'], true)) {
            return (int) $yearId === (int) $ctx['prev'];
        }

        return (int) $yearId === (int) $ctx['current'];
    }
}
