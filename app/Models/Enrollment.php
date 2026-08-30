<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 👈 1. Soft Delete support integration
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough; // 👈 Reverse relationship tracking ke liye

class Enrollment extends Model
{
    use SoftDeletes; // 👈 2. Soft Delete trait active ki

    protected $dates = ['deleted_at'];

    // Mass assignment se bachne aur data insertion ke liye fields
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'group_id',
        'roll_no',
        'enroll_status' // ⚙️ (0=Inactive, 1=Active, 2=Promoted, 3=Detained, 4=Pending Approval)
    ];

    // --- Relationships ---

    /**
     * Link back to the Student Profile
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * 👨‍👦 Direct Parent User Account link via Student and ParentProfile
     * Is se aap enrollment list se seedha parent ka login/auth data (name, username) pull kar sakte hain.
     */
    public function parentUser(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,           // 1. Target Model (Jo data hume aakhir me chahiye)
            Student::class,        // 2. Intermediate Model (Jis raste se hum guzrenge)
            'id',                              // 3. Foreign key on 'students' table (enrollments.student_id -> students.id)
            'id',                              // 4. Foreign key on 'users' table (parent_profiles.user_id -> users.id... handles via student's parent relation)
            'student_id',                      // 5. Local key on 'enrollments' table
            'parent_id'                        // 6. Local key on 'students' table ka jo parent_profiles se link hai
        )->join('parent_profiles', 'users.id', '=', 'parent_profiles.user_id');
        // 💡 Pro Tip: Kyunke rasta 3 tables ka hai (Enrollment -> Student -> ParentProfile -> User), 
        // isliye end me join lagana zaroori hai taake data accuracy 100% professional rahe.
    }

// // Controller ke andar list nikalte waqt:
// $enrollments = Enrollment::with(['student.parent.user', 'schoolClass', 'section'])->get();

    /**
     * Link to the Academic Year / Session
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Link to the School Class
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Link to the Section
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Link to the Group (Optional - Arts, Science, etc.)
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'enrollment_id');
    }

    public function skillMarks()
    {
        return $this->hasMany(StudentSkillMark::class);
    }

    public function result()
    {
        return $this->hasMany(Result::class, 'enrollment_id');
    }
}
