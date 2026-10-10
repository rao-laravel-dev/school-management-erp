<?php

namespace App\Models;

use App\Models\ParentProfile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes; // 👈 Soft Delete active karne ke liye
use Illuminate\Support\Str;

class Student extends Model
{
    use SoftDeletes; // 👈 Soft Delete trait active ki

    protected $dates = ['deleted_at'];
    protected $appends = ['photo_url'];

    protected $fillable = [
        'user_id',
        'parent_id',
        'admission_no',
        'admission_date',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'category_id',      // 👈 fix: category ki jagah
        'house_id',          // 👈 fix: house ki jagah
        'religion',
        'caste',
        'blood_group',
        'height',
        'weight',
        'measurement_date',
        'medical_history',
        'photo',
        'father_photo',
        'mother_photo',
        'guardian_photo',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'is_guardian',
        'guardian_name',
        'guardian_relation',
        'guardian_phone',
        'guardian_email',
        'guardian_address',
        'father_cnic',
        'father_cnic_front',
        'father_cnic_back',
        'mother_cnic',
        'mother_cnic_front',
        'mother_cnic_back',
        'address',
        'status',
        'remarks'
    ];


    // --- Relationships ---

    // User table ke sath link (for login)// 1. Login auth k liye (Sahi)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 2. Parent Profile k liye (Sahi - Mismatch se bachega)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    // 3. History tracking (Sahi)
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    // 4. Aapka advanced features wala method (Zabardast!)
    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class, 'student_id')->latestOfMany();
    }

    /**
     * Student ki attendance ka record
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    // public function examResults() {
    //     return $this->hasMany(ExamResult::class, 'student_id'); // Apna foreign key check kar lein
    // }

    public function fees(): HasMany
    {
        return $this->hasMany(StudentFees::class, 'user_id', 'user_id');
    }

    // Student.php mein ye add karein
    public function schoolClass(): BelongsTo
    {
        // Yahan check kar lein ke aapka Enrollment table mein class_id column ka naam kya hai
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // --- Mutators & Accessors ---

    /**
     * Full Name Accessor
     * Use: $student->full_name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * First Name Mutator (Trim + Lowercase + Capitalize)
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucfirst(strtolower(trim((string)$value))),
        );
    }

    /**
     * Last Name Mutator (Trim + Lowercase + Capitalize)
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucfirst(strtolower(trim((string)$value))),
        );
    }

    public function discounts()
    {
        return $this->hasMany(StudentDiscount::class);
    }

    public function category()
    {
        return $this->belongsTo(StudentCategory::class, 'category_id');
    }

    public function house()
    {
        return $this->belongsTo(StudentHouse::class, 'house_id');
    }

    // Student.php mein add karein
    // Agar QR record nahi bana to bana ke return karo, warna existing wapas karo
    public function getOrCreateQrCode(): string
    {
        $qr = $this->qrCode;

        if (!$qr) {
            $qr = QrCode::create([
                'owner_type' => self::class,
                'owner_id'   => $this->id,
                'code'       => 'STU-' . $this->id . '-' . Str::random(10),
            ]);
        }

        return $qr->code;
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            if (\Storage::disk('public')->exists('uploads/students/' . $this->photo)) {
                return asset('storage/uploads/students/' . $this->photo);
            }
            if (\Storage::disk('public')->exists('students/' . $this->photo)) {
                return asset('storage/students/' . $this->photo);
            }
            if (\Storage::disk('public')->exists('uploads/student_images/' . $this->photo)) { // purane portal uploads
                return asset('storage/uploads/student_images/' . $this->photo);
            }
            if (file_exists(public_path('uploads/students/' . $this->photo))) {
                return asset('uploads/students/' . $this->photo);
            }
        }
        return asset('uploads/no_image.jpg');
    }
}
