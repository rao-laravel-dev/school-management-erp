<?php

namespace App\Models;

use App\Models\Enrollment;
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
        'weekly_off_days'
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
}
