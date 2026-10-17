<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class HomeWork extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'class_id',
        'section_id',
        'subject_id',
        'title',
        'description',
        'homework_date',
        'due_date',
        'attachment',
        'status',
        'created_by',
    ];

    protected $casts = [
        'homework_date' => 'date',
        'due_date'      => 'date',
        'status'        => 'integer',
    ];

    // Images are saved on the public disk (storage/app/public) by ImageService; other files go to public/uploads
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment) {
            return null;
        }

        return Storage::disk('public')->exists($this->attachment)
            ? asset('storage/' . $this->attachment)
            : asset($this->attachment);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
