<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'is_off_day', 'status'];

    protected $casts = [
        'status' => 'boolean',
        'is_off_day' => 'boolean',
    ];

    public function academicCalendars()
    {
        return $this->hasMany(AcademicCalendar::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}