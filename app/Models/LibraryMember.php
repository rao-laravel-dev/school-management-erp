<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    protected $fillable = [
        'member_type',
        'member_id',
        'library_card_no',
        'added_by',
    ];

    protected $appends = ['member_name'];


    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function bookIssues()
    {
        return $this->hasMany(BookIssue::class);
    }

    // member_type 'student' ya 'staff' string hai (class name nahi),
    // isliye Eloquent ka normal morphTo() nahi chalega - accessor banate hain
    public function memberDetail()
    {
        return $this->member_type === 'student'
            ? Student::find($this->member_id)
            : User::find($this->member_id);
    }


    public function getMemberNameAttribute(): string
    {
        if ($this->member_type === 'student') {
            return optional(\App\Models\Student::find($this->member_id))->full_name ?? 'Unknown';
        }

        return optional(\App\Models\User::find($this->member_id))->name ?? 'Unknown';
    }
}
