<?php

namespace App\Models;

use App\Models\PrintReportCard;
use Illuminate\Database\Eloquent\Model;

class ReportCardTemplate extends Model
{
    protected $fillable = [
        'template_name', 'body_text', 'footer_text',
        'header_image',
        'left_sign', 'middle_sign', 'right_sign', 'background_image',
        'show_name', 'show_father_name', 'show_mother_name',
        'show_admission_no', 'show_roll_number', 'show_photo',
        'show_class', 'show_section', 'show_dob',
        'show_attendance_summary', 'show_co_curricular',
        'show_behavioral', 'show_class_teacher_remark',
    ];
 
    protected $casts = [
        'show_name' => 'boolean',
        'show_father_name' => 'boolean',
        'show_mother_name' => 'boolean',
        'show_admission_no' => 'boolean',
        'show_roll_number' => 'boolean',
        'show_photo' => 'boolean',
        'show_class' => 'boolean',
        'show_section' => 'boolean',
        'show_dob' => 'boolean',
        'show_attendance_summary' => 'boolean',
        'show_co_curricular' => 'boolean',
        'show_behavioral' => 'boolean',
        'show_class_teacher_remark' => 'boolean',
    ];
 
    public function printReportCards()
    {
        return $this->hasMany(PrintReportCard::class, 'template_id');
    }
    
}
