<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintReportCard extends Model
{
    protected $fillable = [
        'enrollment_id', 'exam_id', 'template_id',
        'class_teacher_remark', 'issued_by', 'issued_at',
    ];
 
    protected $casts = [
        'issued_at' => 'datetime',
    ];
 
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
 
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
 
    public function template()
    {
        return $this->belongsTo(ReportCardTemplate::class, 'template_id');
    }
 
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
