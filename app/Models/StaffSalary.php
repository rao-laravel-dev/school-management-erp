<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSalary extends Model
{
    protected $fillable = [
        'user_id', 'basic_salary', 'allowance', 'deduction', 'advance_balance'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
