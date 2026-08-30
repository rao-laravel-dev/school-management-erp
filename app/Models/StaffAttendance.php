<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{

    protected $fillable = ['user_id', 'date', 'status', 'time_out', 'marked_at', 'remarks', 'marked_by'];

    protected $casts = [
        'date'      => 'date',
        'time_out'  => 'datetime:H:i',
        'marked_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
