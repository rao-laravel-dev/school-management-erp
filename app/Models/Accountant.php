<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accountant extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'accountant_id',
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

    // Accountant ka User se link
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
}
