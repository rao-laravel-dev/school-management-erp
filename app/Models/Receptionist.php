<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receptionist extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'receptionists';

    // 👈 Ya to aap saari fields ko fillable array me dalein, ya guaded ko khali chor dein
    // $guarded khali chorne se saari fields mass assignment ke liye khul jati hain (Best & Quickest)
    protected $guarded = [];

    // Agar aap fillable use karna chahte hain to is tarah likhein:

    protected $fillable = [
        'user_id',
        'receptionist_id',
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
        'alt_phone',
        'emergency_name',
        'emergency_relation',
        'emergency_phone',
        'address',
        'permanent_address',
        'desk_number',
        'qualification',
        'work_experience',
        'joining_date',
        'photo',
        'contract_type',
        'work_shift'
    ];


    // User Relation
   public function user() {
    return $this->belongsTo(User::class, 'user_id', 'id');
}

    // 2. Salary Relation (Agar model ka naam StaffSalary hai)
    public function salary()
    {
        return $this->hasOne(StaffSalary::class, 'user_id', 'user_id');
    }

    // 3. Bank Detail Relation (Agar model ka naam StaffBankDetail hai)
    public function bankDetail()
    {
        return $this->hasOne(StaffBankDetail::class, 'user_id', 'user_id');
    }
}
