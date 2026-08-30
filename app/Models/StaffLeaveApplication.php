<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLeaveApplication extends Model
{
    protected $fillable = [
    'user_id', 'apply_date', 'leave_type', 'from_date', 'to_date', 
    'leave_duration', 'total_days', 'reason', 'document', 'status'
];
    public function teacher() {
    return $this->belongsTo(Teacher::class, 'teacher_id');
}
public function user()
{
    // 'user_id' aapke staff_leave_applications table ka column hai
    return $this->belongsTo(User::class, 'user_id');
}
}
