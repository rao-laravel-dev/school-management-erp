<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes; // Enable SoftDeletes

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'teacher_id',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'email',
        'cnic',
        'gender',
        'dob',
        'marital_status',
        'phone',
        // Naye fields yahan add karein:
        'emergency_name',
        'emergency_relation',
        'emergency_phone',
        'address',
        'permanent_address',
        'qualification',
        'work_experience',
        'joining_date',
        'photo',
        'contract_type', // 👈 Yeh add karein
        'work_shift'     // 👈 Yeh add karein
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
    ];

    protected $appends = ['name'];

    // Teacher ka User se link
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Teacher assignments
    // Teacher ki saari assignments (Classes/Subjects)
    public function assignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    public function leaveSettings()
    {
        return $this->hasOne(StaffLeaveSetting::class, 'teacher_id');
    }

    public function leaveApplications()
    {
        return $this->hasMany(StaffLeaveApplication::class, 'teacher_id');
    }

    public function bankDetail()
    {
        return $this->hasOne(StaffBankDetail::class, 'user_id', 'user_id');
    }
    public function salary()
    {
        return $this->hasOne(StaffSalary::class, 'user_id', 'user_id');
    }


    // First Name Accessor
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst(strtolower($value)),
        );
    }

    // Last Name Accessor
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst(strtolower($value)),
        );
    }

    // Full Name Accessor — Blade views mein $teacher->name seedha use karne ke liye
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    // Photo URL Accessor — Checks storage/teacher_images, uploads, and falls back to no_image.jpg
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->photo) {
                    if (\Storage::disk('public')->exists('teacher_images/' . $this->photo)) {
                        return asset('storage/teacher_images/' . $this->photo);
                    }
                    if (file_exists(public_path('uploads/teacher_images/' . $this->photo))) {
                        return asset('uploads/teacher_images/' . $this->photo);
                    }
                    if (file_exists(public_path('uploads/teachers/' . $this->photo))) {
                        return asset('uploads/teachers/' . $this->photo);
                    }
                }
                return asset('uploads/no_image.jpg');
            }
        );
    }
}
