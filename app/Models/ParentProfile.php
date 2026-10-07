<?php

namespace App\Models;

use App\Models\User;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParentProfile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
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
        'father_photo',
        'mother_photo',
        'guardian_photo',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    // ==========================================
    // 🎯 ACCESSORS & MUTATORS (Modern Laravel style)
    // ==========================================

    /**
     * Father Name: Standardize to lowercase in DB, Title Case on display.
     */
    protected function fatherName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    /**
     * Mother Name: Standardize to lowercase in DB, Title Case on display.
     */
    protected function motherName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? ucwords($value) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    /**
     * Guardian Name: Standardize to lowercase in DB, Title Case on display.
     */
    protected function guardianName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? ucwords($value) : null,
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    // ==========================================
    // 🔗 RELATIONSHIPS
    // ==========================================

    /**
     * Link back to the independent Users authentication table.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Link to all active or inactive students tied to this parent (Siblings logic).
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function getFatherPhotoUrlAttribute(): string
    {
        return $this->resolvePhoto($this->father_photo);
    }

    public function getMotherPhotoUrlAttribute(): string
    {
        return $this->resolvePhoto($this->mother_photo);
    }

    public function getGuardianPhotoUrlAttribute(): string
    {
        return $this->resolvePhoto($this->guardian_photo);
    }

    private function resolvePhoto(?string $filename): string
    {
        if ($filename) {
            $dirs = ['uploads/parents', 'uploads/parent_images', 'uploads/students', 'parents', 'students'];
            foreach ($dirs as $dir) {
                if (\Storage::disk('public')->exists($dir . '/' . $filename)) {
                    return asset('storage/' . $dir . '/' . $filename);
                }
                if (file_exists(public_path($dir . '/' . $filename))) {
                    return asset($dir . '/' . $filename);
                }
            }
        }
        return asset('uploads/no_image.jpg');
    }
}