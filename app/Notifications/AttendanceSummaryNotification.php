<?php

namespace App\Notifications;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

// Admin / superadmin ko class ya staff attendance ka ek summary (kabhi per-student nahi). Bell + push
class AttendanceSummaryNotification extends AppNotification
{
    // Staff list se bahar ke roles (StaffAttendanceController::$excludedRoles jaisi list)
    private const STAFF_EXCLUDED_ROLES = ['superadmin', 'admin', 'parent', 'student', 'user'];

    // Sirf plain strings: queue job mein model dobara load nahi hota aur bell + push ka text same rehta hai
    protected string $titleText;
    protected string $bodyText;
    protected string $urlPath;
    protected ?string $imagePath;

    public function __construct(string $titleText, string $bodyText, string $urlPath, ?string $imagePath = null)
    {
        parent::__construct();

        $this->titleText = $titleText;
        $this->bodyText = $bodyText;
        $this->urlPath = $urlPath;
        $this->imagePath = $imagePath;
    }

    // Save karne wale (actor) ki photo ka public/ relative path; photo nahi (default no-image) = null, bell attendance icon dikhata hai
    protected static function actorImage(?User $actor): ?string
    {
        if (!$actor) {
            return null;
        }

        $path = Str::after($actor->photo_url, asset(''));

        return $path === 'uploads/no_image.jpg' ? null : $path;
    }

    // Active admin + superadmin (superadmin lists se hidden hai magar notification leta hai); save karne wala khud nahi
    public static function recipients(?int $exceptUserId = null)
    {
        return User::role(['admin', 'superadmin'])
            ->where('status', 1)
            ->when($exceptUserId, fn($q) => $q->where('id', '!=', $exceptUserId))
            ->get();
    }

    // $source: "Manual by Name (Role)" ya "Scan summary at 9:00 AM"; $updated = us din pehle summary ja chuka (chhota "updated" note)
    public static function forClass($classId, $sectionId, string $date, string $source, bool $updated = false, ?User $actor = null): self
    {
        $class = SchoolClass::find($classId);
        $section = Section::find($sectionId);
        $classSection = trim(($class->name ?? '') . ' - ' . ($section->name ?? ''), ' -');

        // Active students (dailyAttendance wali same query): total aur "not marked" (koi row nahi) ka hisab
        $total = Enrollment::whereHas('student')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where(fn($q) => AcademicYear::whereClassSession($q))
            ->where('enroll_status', 1)
            ->count();

        $rows = Attendance::query()
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('attendance_date', $date)
            ->whereHas('enrollment', fn($q) => AcademicYear::whereClassSession($q))
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $present = (int) $rows->get(Attendance::STATUS_PRESENT, 0);
        $late = (int) $rows->get(Attendance::STATUS_LATE, 0);
        $absent = (int) $rows->get(Attendance::STATUS_ABSENT, 0);
        $half = (int) $rows->get(Attendance::STATUS_HALF_DAY, 0);
        $leave = (int) $rows->get(Attendance::STATUS_LEAVE, 0);
        $notMarked = max(0, $total - $rows->sum());

        $url = route('attendance.daily', ['class_id' => $classId, 'section_id' => $sectionId, 'date' => $date], false);

        if ($updated) {
            return new self(
                "{$classSection} attendance updated",
                "{$source} at " . now()->format('g:i A') . ". Now {$present} present, {$late} late, {$absent} absent, {$half} half day, {$leave} leave, {$notMarked} not marked.",
                $url,
                self::actorImage($actor)
            );
        }

        return new self(
            "{$classSection} attendance (" . Carbon::parse($date)->format('d-m-Y') . ')',
            "{$source}: {$present} present, {$late} late, {$absent} absent, {$half} half day, {$leave} leave, {$notMarked} not marked ({$total} students).",
            $url,
            self::actorImage($actor)
        );
    }

    // Staff: unscanned staff ka row hi nahi hota, isliye "not marked" = active staff minus rows
    public static function forStaff(string $date, string $source): self
    {
        $total = User::whereHas('roles', fn($q) => $q->whereNotIn('name', self::STAFF_EXCLUDED_ROLES))
            ->where('status', 1)
            ->count();

        $rows = StaffAttendance::whereDate('date', $date)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $notMarked = max(0, $total - $rows->sum());

        return new self(
            'Staff attendance (' . Carbon::parse($date)->format('d-m-Y') . ')',
            "{$source}: " . (int) $rows->get('present', 0) . ' present, ' . (int) $rows->get('leave', 0) . ' leave, '
                . (int) $rows->get('absent', 0) . ' absent, ' . (int) $rows->get('half_day', 0) . " half day, {$notMarked} not marked ({$total} staff).",
            route('staff_attendance.index', ['date' => $date], false)
        );
    }

    protected function title(object $notifiable): string
    {
        return $this->titleText;
    }

    protected function body(object $notifiable): string
    {
        return $this->bodyText;
    }

    protected function url(object $notifiable): string
    {
        return $this->urlPath;
    }

    protected function image(object $notifiable): ?string
    {
        return $this->imagePath;
    }
}
