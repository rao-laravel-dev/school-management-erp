<?php

namespace App\Models;

use App\Models\Purpose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Visitor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'id_card',
        'no_of_person',
        'date',
        'in_time',
        'out_time',
        'purpose_id',
        'meeting_with_type',
        'meeting_with_id',
        'created_by',
        'updated_by'
    ];

    // meeting_with_type (role ka naam) -> profile model
    private const PROFILE_MODELS = [
        'student'      => Student::class,
        'teacher'      => Teacher::class,
        'receptionist' => Receptionist::class,
        'accountant'   => Accountant::class,
        'librarian'    => Librarian::class,
    ];

    public function purpose()
    {
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    // Index ke liye: visitor id => "Meeting With" naam, har type ki sirf ek query (N+1 nahi)
    public static function meetingNames($visitors): array
    {
        $names = [];

        foreach ($visitors->groupBy('meeting_with_type') as $type => $rows) {
            $model = self::PROFILE_MODELS[strtolower((string) $type)] ?? null;
            if (!$model) {
                continue;
            }

            $people = $model::withTrashed()
                ->whereIn('id', $rows->pluck('meeting_with_id')->filter()->unique())
                ->with(['user' => function ($q) {
                    $q->withTrashed()->select('id', 'name');
                }])
                ->get(['id', 'user_id', 'first_name', 'last_name'])
                ->keyBy('id');

            foreach ($rows as $row) {
                $person = $people->get($row->meeting_with_id);
                if ($person) {
                    // Asli naam user record se (profile accessor ucfirst/lowercase kar deta hai)
                    $names[$row->id] = $person->user
                        ? $person->user->name
                        : trim($person->first_name . ' ' . $person->last_name);
                }
            }
        }

        return $names;
    }

    public function getMeetingPersonNameAttribute()
    {
        if (empty($this->meeting_with_type) || empty($this->meeting_with_id)) {
            return '-';
        }

        // Student
        if ($this->meeting_with_type === 'student') {

            $student = DB::table('students')
                ->where('id', $this->meeting_with_id)
                ->first();

            return $student
                ? trim($student->first_name . ' ' . $student->last_name)
                : '-';
        }

        // Staff (teacher, accountant, librarian, receptionist)
        $table = strtolower($this->meeting_with_type);

        // teacher => teachers
        if (!Schema::hasTable($table)) {
            $table .= 's';
        }

        if (!Schema::hasTable($table)) {
            return '-';
        }

        $person = DB::table($table)
            ->where('id', $this->meeting_with_id)
            ->first();

        if (!$person) {
            return '-';
        }

        return trim(($person->first_name ?? '') . ' ' . ($person->last_name ?? ''));
    }
}
