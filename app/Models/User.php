<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\StaffLeaveSetting;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes; //  1. Soft Delete integration
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read \App\Models\Student $studentProfile
 * @property-read \App\Models\ParentProfile $parentProfile
 */

class User extends Authenticatable
{
    use Notifiable, HasRoles, SoftDeletes, HasApiTokens; //  2. Soft Delete trait active ki

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'username', // 🪪 (Student ke liye Admission No, Parent ke liye CNIC save hoga)
        'email_verified_at',
        'password',
        'photo',
        'phone',
        'address',
        'gender',
        'status', // ⚙️ (0 = Inactive, 1 = Active, 2 = Pending Admin Approval)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELATIONSHIPS ---

    /**
     * 🎓 Agar yeh user 'student' hai, to iski main student profile details
     * // Student profile fetch karne k liye
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    // Parent profile fetch karne k liye
    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentProfile::class, 'user_id');
    }

    // --- MUTATORS & ACCESSORS ---

    // Name
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    // Address
    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = $value
            ? ucwords(strtolower(trim($value)))
            : null;
    }

    // Gender
    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = strtolower(trim($value));
    }

    public function getStaffCodeAttribute()
    {
        $roleName = $this->roles->first()->name ?? null;

        return match ($roleName) {
            'teacher'      => $this->teacher?->teacher_id ?? '-',
            'accountant'   => $this->accountant?->accountant_id ?? '-',
            'receptionist' => $this->receptionist?->receptionist_id ?? '-',
            default        => '-',
        };
    }

    public function getPhotoUrlAttribute()
    {
        $role = $this->roles->first()->name ?? null;

        if ($role && method_exists($this, $role)) {
            $related = $this->$role; // e.g. $this->teacher, $this->accountant

            if ($related && $related->photo) {
                return asset('uploads/' . $role . '_images/' . $related->photo);
            }
        }

        return asset('images/no-image.png');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }
    // User.php mein, doosre relations ke sath add karo (e.g. teacher() ke paas)
    public function qrCode()
    {
        return $this->morphOne(\App\Models\QrCode::class, 'owner');
    }

    public function bankDetail()
    {
        return $this->hasOne(StaffBankDetail::class);
    }

    // User.php
    public function salaries()
    {
        return $this->hasOne(StaffSalary::class, 'user_id');
    }

    public function staffAttendances()
    {
        return $this->hasMany(StaffAttendance::class, 'user_id');
    }

    // User.php mein ye function add karein
    public function leaveSettings()
    {
        // 'user_id' wo column hai jo aapki staff_leave_settings table mein hai
        return $this->hasMany(StaffLeaveSetting::class, 'user_id', 'id');
    }

    public function receptionist()
    {
        return $this->hasOne(Receptionist::class, 'user_id', 'id');
    }

    public function accountant()
    {
        return $this->hasOne(Accountant::class, 'user_id', 'id');
    }

    public function timetables()
    {
        return $this->hasMany(ClassTimetable::class, 'teacher_id');
    }

    // User.php mein add karein
    public function getOrCreateQrCode(): string
    {
        $qr = $this->qrCode;

        if (!$qr) {
            $qr = QrCode::create([
                'owner_type' => self::class,
                'owner_id'   => $this->id,
                'code'       => 'STF-' . $this->id . '-' . Str::random(10),
            ]);
        }

        return $qr->code;
    }

    // Status 0 (inactive) / 2 (pending) login nahi kar sakte; superadmin kabhi block nahi
    public function isLoginBlocked(): bool
    {
        return in_array((int) $this->status, [0, 2], true) && ! $this->hasRole('superadmin');
    }
}
