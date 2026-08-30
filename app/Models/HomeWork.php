<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
