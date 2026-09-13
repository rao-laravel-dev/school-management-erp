<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'school_name',
        'logo',
        'principal_signature',
        'address',
        'phone',
        'email',
        'website',
        'registration_no',
        'established_year',
        'admission_prefix',
        'header_note',
        'footer_note',
    ];

    // Helper: hamesha wahi single row lao, agar exist nahi karti to bana do
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], ['school_name' => 'My School']);
    }
}
