<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffIdCardTemplate extends Model
{
    protected $fillable = [
        'title', 'background_image', 'logo', 'signature',
        'school_name', 'address_phone_email', 'header_color', 'design_type',
        'show_staff_name', 'show_staff_id', 'show_designation', 'show_department',
        'show_father_name', 'show_mother_name', 'show_date_of_joining',
        'show_current_address', 'show_phone', 'show_dob', 'show_qr_code',
        'show_barcode', 'status',
    ];
}
