<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'event_type_id',   // ✅ 'event_type' ki jagah
        'all_day',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'all_day'    => 'boolean',
        'status'     => 'boolean',
    ];

    // ---------- Relationships ----------
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function eventType()   // ✅ naya relation add kiya
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ---------- Scopes ----------
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }
}