<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDiscount extends Model
{
    protected $fillable = [
        'student_id',
        'discount_policy_id',
        'remarks',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function discountPolicy()
    {
        return $this->belongsTo(DiscountPolicy::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}