<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class StaffLeaveSetting extends Model
{
    protected $fillable = [
        'user_id', 
        'role_id',
        'leave_type', 
        'total_days'
    ];

    // Staff (User) ke sath relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        // Agar aap Spatie use kar rahe hain to Spatie ka Role model use karein
        return $this->belongsTo(Role::class, 'role_id');
    }
}
