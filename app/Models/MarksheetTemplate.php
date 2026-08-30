<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarksheetTemplate extends Model
{
    protected $table = 'marksheet_templates';

    protected $fillable = [
        'template_name',
        'exam_name',
        'school_name',
        'exam_center',
        'body_text',
        'footer_text',
        'printing_date',
        'header_image',
        'left_logo',
        'right_logo',
        'left_sign',
        'middle_sign',
        'right_sign',
        'background_image',
        'show_name',
        'show_father_name',
        'show_mother_name',
        'show_exam_session',
        'show_admission_no',
        'show_division',
        'show_rank',
        'show_roll_number',
        'show_photo',
        'show_class',
        'show_section',
        'show_dob',
        'show_remark',
    ];

    protected $casts = [
        'printing_date' => 'date',
        'show_name' => 'boolean',
        'show_father_name' => 'boolean',
        'show_mother_name' => 'boolean',
        'show_exam_session' => 'boolean',
        'show_admission_no' => 'boolean',
        'show_division' => 'boolean',
        'show_rank' => 'boolean',
        'show_roll_number' => 'boolean',
        'show_photo' => 'boolean',
        'show_class' => 'boolean',
        'show_section' => 'boolean',
        'show_dob' => 'boolean',
        'show_remark' => 'boolean',
    ];
}
