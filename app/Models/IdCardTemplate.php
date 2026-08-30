<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdCardTemplate extends Model
{
    protected $fillable = [
        'title', 'background_image', 'logo', 'signature',
        'school_name', 'address_phone_email', 'header_color', 'design_type',
        'show_admission_no', 'show_student_name', 'show_class', 'show_father_name',
        'show_mother_name', 'show_address', 'show_phone', 'show_dob',
        'show_blood_group', 'show_roll_no', 'show_house', 'show_qr_code',
        'show_barcode', 'status',
    ];
}
