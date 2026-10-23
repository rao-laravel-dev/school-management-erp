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

        // WithTrashed profile: soft-deleted staff ki history mein bhi Staff ID dikhe
        return match ($roleName) {
            'teacher'      => $this->teacherWithTrashed?->teacher_id ?? '-',
            'accountant'   => $this->accountantWithTrashed?->accountant_id ?? '-',
            'receptionist' => $this->receptionistWithTrashed?->receptionist_id ?? '-',
            'librarian'    => $this->librarianWithTrashed?->librarian_id ?? '-',
            default        => '-',
        };
    }

    // Superadmin = hidden system owner (Gate::before, login never blocked)
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    // Lists/counts/dropdowns se superadmin hide karne ke liye
    public function scopeWithoutSuperadmin($query)
    {
        return $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'superadmin');
        });
    }

    // Dropdown ke liye active staff: status 1, user + staff profile trashed nahi, role label ke liye roles eager load
    public function scopeActiveStaff($query, array $roles)
    {
        return $query->withoutSuperadmin()
            ->role($roles)
            ->where('status', 1)
            ->where(function ($q) {
                $q->whereHas('teacher')
                    ->orWhereHas('receptionist')
                    ->orWhereHas('accountant')
                    ->orWhereHas('librarian');
            })
            ->with('roles')
            ->orderBy('name');
    }

    // Superadmin guard: koi aur usay edit/block/delete nahi kar sakta, aur wo khud ko lockout nahi kar sakta.
    // Console/tinker (no logged-in user) allowed rakha hai taake DB recovery ho sake.
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if (! $user->isSuperAdmin() || ! auth()->check()) {
                return;
            }

            // Doosre user ka superadmin par koi bhi change band
            if ((int) auth()->id() !== (int) $user->id) {
                abort(403, 'Super Admin cannot be modified.');
            }

            // Khud ko inactive/pending karna band (lockout protection)
            if ($user->isDirty('status') && (int) $user->status !== 1) {
                abort(403, 'Super Admin cannot be deactivated.');
            }
        });

        static::deleting(function (User $user) {
            if ($user->isSuperAdmin() && auth()->check()) {
                abort(403, 'Super Admin cannot be deleted.');
            }
        });
    }

    // Trashed badge: users row trashed ya staff profile (teacher/accountant/receptionist) soft-deleted
    public function isStaffTrashed(): bool
    {
        // Superadmin kabhi trashed/blocked nahi ginna
        if ($this->isSuperAdmin()) {
            return false;
        }

        if ($this->trashed()) {
            return true;
        }

        $roleName = $this->roles->first()->name ?? null;

        $profile = match ($roleName) {
            'teacher'      => $this->teacherWithTrashed,
            'accountant'   => $this->accountantWithTrashed,
            'receptionist' => $this->receptionistWithTrashed,
            'librarian'    => $this->librarianWithTrashed,
            default        => null,
        };

        return $profile ? $profile->trashed() : false;
    }

    public function getPhotoUrlAttribute()
    {
        $role = $this->roles->first()->name ?? null;

        // Teacher: Teachers list wala same accessor (storage teacher_images -> public fallbacks)
        if ($role === 'teacher' && $this->teacher) {
            return $this->teacher->photo_url;
        }

        // Parent: asli photo parent_profiles.father_photo mein hoti hai (student edit page wali), users.photo baad mein
        if ($role === 'parent' && $this->parentProfile && $this->parentProfile->father_photo) {
            return $this->parentProfile->father_photo_url;
        }

        // Accountant/Receptionist ki photo apni table mein, baqi roles (librarian/admin/...) ki users.photo mein
        $photo = $this->photo;
        if (in_array($role, ['accountant', 'receptionist', 'librarian'], true) && $this->$role && $this->$role->photo) {
            $photo = $this->$role->photo;
        }

        if ($photo) {
            // ImageService storage (public disk) pe likhta hai; purani photos public/uploads mein hain
            $folders = [
                'accountant'   => ['uploads/accountant_images', 'uploads/accountants'],
                'receptionist' => ['uploads/receptionist_images', 'uploads/reception_images', 'uploads/receptionists'],
                // Superadmin shared admin profile page use karta hai, jo uploads/admin_images mein save karta hai
                'superadmin'   => ['uploads/superadmin_images', 'uploads/admin_images'],
            ][$role] ?? ['uploads/' . $role . '_images'];

            foreach ($folders as $folder) {
                if (\Storage::disk('public')->exists($folder . '/' . $photo)) {
                    return asset('storage/' . $folder . '/' . $photo);
                }
                if (file_exists(public_path($folder . '/' . $photo))) {
                    return asset($folder . '/' . $photo);
                }
            }
        }

        return asset('uploads/no_image.jpg');
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

    public function librarian()
    {
        return $this->hasOne(Librarian::class, 'user_id', 'id');
    }

    // WithTrashed versions: staff_code / isStaffTrashed() soft-deleted profile ke liye (active lists ke relations unchanged)
    public function teacherWithTrashed()
    {
        return $this->hasOne(Teacher::class, 'user_id')->withTrashed();
    }

    public function receptionistWithTrashed()
    {
        return $this->hasOne(Receptionist::class, 'user_id', 'id')->withTrashed();
    }

    public function accountantWithTrashed()
    {
        return $this->hasOne(Accountant::class, 'user_id', 'id')->withTrashed();
    }

    public function librarianWithTrashed()
    {
        return $this->hasOne(Librarian::class, 'user_id', 'id')->withTrashed();
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
            $this->setRelation('qrCode', $qr); // same instance par dobara call (QR + barcode) naya row na banaye
        }

        return $qr->code;
    }

    // Active admin users (role admin, status 1, not trashed, superadmin excluded)
    public static function activeAdminCount(): int
    {
        return static::withoutSuperadmin()->role('admin')->where('status', 1)->count();
    }

    // School lock: koi bhi admin user active nahi = har non-superadmin user login blocked
    public static function isSchoolLocked(): bool
    {
        return static::activeAdminCount() === 0;
    }

    // Status 0 (inactive) / 2 (pending) login nahi kar sakte; superadmin kabhi block nahi
    public function isLoginBlocked(): bool
    {
        if ($this->hasRole('superadmin')) {
            return false;
        }

        // School locked (no active admin): sab non-superadmin blocked, kisi ka users.status change nahi hota
        if (static::isSchoolLocked()) {
            return true;
        }

        // Role-level block: sirf tab jab user ke SAARE roles disabled hon (roles.is_active = 0)
        $allRolesDisabled = $this->roles->isNotEmpty() && $this->enabledRoleNames()->isEmpty();

        return in_array((int) $this->status, [0, 2], true) || $allRolesDisabled;
    }

    // Enabled roles ke naam (roles.is_active); Switch Role / layout / dashboard sirf inhi se
    public function enabledRoleNames()
    {
        return $this->roles->filter(function ($role) {
            return (int) ($role->is_active ?? 1) === 1;
        })->pluck('name')->values();
    }

    // Active role = pehla ENABLED role (disabled role skip), warna null
    public function activeRoleName(): ?string
    {
        return $this->enabledRoleNames()->first();
    }

    // "Marked by" label: "Rao Imran (Teacher)". Role stored nahi hota, display time par active role (layout wala same rule); superadmin = Admin
    public function getNameWithRoleAttribute(): string
    {
        $role = $this->activeRoleName();
        if ($role === 'superadmin') {
            $role = 'admin';
        }

        return $role ? $this->name . ' (' . ucfirst($role) . ')' : (string) $this->name;
    }

    public function hasEnabledRole(string $name): bool
    {
        return $this->enabledRoleNames()->contains($name);
    }
}
