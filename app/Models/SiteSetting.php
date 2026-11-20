<?php

namespace App\Models;

use App\Services\ImageService;
use App\Traits\HasCardColors;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasCardColors; // T340a: validCardHex() + contrastText() for the theme colours

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
        // T340a: tagline + theme; login_* are used by T340b
        'tagline',
        'theme_palette',
        'primary_color',
        'accent_color',
        'font_family',
        'sidebar_style',
        'login_heading',
        'login_message',
        'login_background',
    ];

    // T340a: palette key => [label, primary, accent]. 'default' = today's look. 'custom' = the 2 colour pickers.
    public const PALETTES = [
        'default'    => ['Classic Blue', '#008cff', '#ffb020'],
        'indigo'     => ['Indigo', '#4f46e5', '#f59e0b'],
        'royal_blue' => ['Royal Blue', '#1d4ed8', '#facc15'],
        'emerald'    => ['Emerald', '#059669', '#f59e0b'],
        'teal'       => ['Teal', '#0d9488', '#f97316'],
        'crimson'    => ['Crimson', '#dc2626', '#fbbf24'],
        'maroon'     => ['Maroon', '#7f1d1d', '#d4a017'],
        'purple'     => ['Purple', '#7c3aed', '#f472b6'],
        'navy_gold'  => ['Navy & Gold', '#1e3a8a', '#d4af37'],
        'sunset'     => ['Sunset', '#ea580c', '#be185d'],
    ];

    // T340a: font key => [label, css font stack, Google Fonts family or null (system font, no request)]
    public const FONTS = [
        'roboto'            => ['Roboto', 'Roboto, sans-serif', 'Roboto'],
        'plus_jakarta_sans' => ['Plus Jakarta Sans', '"Plus Jakarta Sans", sans-serif', 'Plus+Jakarta+Sans'],
        'poppins'           => ['Poppins', 'Poppins, sans-serif', 'Poppins'],
        'inter'             => ['Inter', 'Inter, sans-serif', 'Inter'],
        'nunito'            => ['Nunito', 'Nunito, sans-serif', 'Nunito'],
        'manrope'           => ['Manrope', 'Manrope, sans-serif', 'Manrope'],
        'lexend'            => ['Lexend', 'Lexend, sans-serif', 'Lexend'],
        'outfit'            => ['Outfit', 'Outfit, sans-serif', 'Outfit'],
        'times_new_roman'   => ['Times New Roman', '"Times New Roman", Times, serif', null],
        'playfair_display'  => ['Playfair Display', '"Playfair Display", serif', 'Playfair+Display'],
    ];

    // light = today's sidebar, dark = existing html.semi-dark, brand = existing html.color-sidebar + school colour
    public const SIDEBAR_STYLES = ['light' => 'Light', 'dark' => 'Dark', 'brand' => 'School colour'];

    public const THEME_DEFAULTS = [
        'theme_palette' => 'default',
        'primary_color' => '#008cff',
        'accent_color'  => '#ffb020',
        'font_family'   => 'roboto',
        'sidebar_style' => 'light',
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

    // T340b: login page background picture (left side of the role login pages): URL or null
    public function getLoginBackgroundUrlAttribute(): ?string
    {
        return ImageService::url($this->login_background, 'site_setting');
    }

    // T340a: safe theme values (bad / empty column values fall back to today's look). Round 2 prints these in <head>.
    public function getThemeAttribute(): array
    {
        $d = self::THEME_DEFAULTS;
        $primary = self::validCardHex($this->primary_color) ? strtolower($this->primary_color) : $d['primary_color'];
        $accent  = self::validCardHex($this->accent_color) ? strtolower($this->accent_color) : $d['accent_color'];
        $font    = self::FONTS[$this->font_family] ?? self::FONTS[$d['font_family']];
        $sidebar = array_key_exists($this->sidebar_style, self::SIDEBAR_STYLES) ? $this->sidebar_style : $d['sidebar_style'];

        return [
            'primary'       => $primary,
            'accent'        => $accent,
            'primary_rgb'   => hexdec(substr($primary, 1, 2)) . ' ' . hexdec(substr($primary, 3, 2)) . ' ' . hexdec(substr($primary, 5, 2)),
            'on_primary'    => self::contrastText($primary),
            'font_stack'    => $font[1],
            'font_url'      => $font[2] ? 'https://fonts.googleapis.com/css2?family=' . $font[2] . ':wght@400;500;600;700&display=swap' : null,
            'sidebar_class' => ['light' => '', 'dark' => 'semi-dark', 'brand' => 'color-sidebar ss-sidebar-brand'][$sidebar],
        ];
    }

    // Helper: hamesha wahi single row lao, agar exist nahi karti to bana do
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], ['school_name' => 'My School']);
    }
}
