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
        'meeting_with_id'
    ];

    public function purpose()
    {
        return $this->belongsTo(Purpose::class, 'purpose_id');
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
