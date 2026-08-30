<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintMarksheet extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'exam_id',
        'class_id',
        'section_id',
        'enrollment_id',
        'template_id',
        'printed_by',
        'printed_at',
    ];
 
    protected $casts = [
        'printed_at' => 'datetime',
    ];
 
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
 
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
 
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
 
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
 
    public function template()
    {
        return $this->belongsTo(MarksheetTemplate::class, 'template_id');
    }
 
    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
