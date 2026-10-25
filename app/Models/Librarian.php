<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Librarian extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'librarian_id',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'email',
        'phone',
        'cnic',
        'gender',
        'dob',
        'marital_status',
        'address',
        'permanent_address',
        'emergency_name',
        'emergency_relation',
        'emergency_phone',
        'qualification',
        'work_experience',
        'joining_date',
        'contract_type',
        'work_shift',
        'photo'
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
    ];

    protected $appends = ['name'];

    // Librarian ka User se link
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bankDetail()
    {
        return $this->hasOne(StaffBankDetail::class, 'user_id', 'user_id');
    }

    public function salary()
    {
        return $this->hasOne(StaffSalary::class, 'user_id', 'user_id');
    }

    // Photo URL Accessor (Accountant wala pattern) — storage/uploads/librarian_images, phir public same folder, warna no_image.jpg
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->photo) {
                    if (file_exists(public_path('uploads/librarian_images/' . $this->photo))) {
                        return asset('uploads/librarian_images/' . $this->photo);
                    }
                }
                return asset('uploads/no_image.jpg');
            }
        );
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

    // Full Name Accessor — Blade views mein $librarian->name seedha use karne ke liye
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}"),
        );
    }
}
