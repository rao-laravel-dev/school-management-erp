<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'school_name',
        'logo',
        'icon',
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

    // Wide school logo (documents) / square school icon (sidebar, role logins, favicon): URL ya null agar set nahi / file missing
    public function getLogoUrlAttribute(): ?string
    {
        return ImageService::url($this->logo, 'site_setting');
    }

    public function getIconUrlAttribute(): ?string
    {
        return ImageService::url($this->icon, 'site_setting');
    }

    // Helper: hamesha wahi single row lao, agar exist nahi karti to bana do
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], ['school_name' => 'My School']);
    }
}
