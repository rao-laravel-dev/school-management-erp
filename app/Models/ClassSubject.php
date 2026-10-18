<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    // 1. Table ka naam define karein
    protected $table = 'class_subject';

    // 2. Fillable fields (jo database mein store honi hain)
    protected $fillable = [
        'class_id', 
        'subject_id', 
        'group_id',
        'subject_code',
        'full_marks',
        'pass_marks'
    ];

    // Is class ke maujooda codes: subject_id => subject_code (sirf wo jinka code set hai)
    public static function codeMapForClass(int $classId): array
    {
        return static::where('class_id', $classId)
            ->whereNotNull('subject_code')
            ->orderBy('id')
            ->pluck('subject_code', 'subject_id')
            ->all();
    }

    // Prefix + agla free 2-digit serial. $usedCodes = is class mein pehle se use hue saare codes
    public static function nextSubjectCode(SchoolClass $class, array $usedCodes): string
    {
        $prefix = (string) $class->subject_prefix;
        $max = 0;
        foreach ($usedCodes as $code) {
            $serial = substr((string) $code, strlen($prefix));
            if (strpos((string) $code, $prefix) === 0 && ctype_digit($serial)) {
                $max = max($max, (int) $serial);
            }
        }

        return $prefix . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }


    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
