<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolTiming extends Model
{
    protected $fillable = [
        'season_name',
        'start_time',
        'end_time',
        'late_grace_minutes',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function active()
    {
        return static::where('is_active', true)->first();
    }
}