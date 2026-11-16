<?php

namespace App\Traits;

// T338: ID card colours (student + staff templates).
// Main = header_color, Accent = accent_color. Text on the Main colour turns white/black by brightness.
trait HasCardColors
{
    public const CARD_DEFAULT_MAIN   = '#4b3bdb';
    public const CARD_DEFAULT_ACCENT = '#ffb020';

    // Only a full #rrggbb is printed into the card <style> block (anything else falls back to the default)
    protected static function validCardHex(?string $hex): bool
    {
        return is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1;
    }

    // '#000000' or '#ffffff', whichever has the higher WCAG contrast ratio on $hex; invalid value => '#ffffff'
    public static function contrastText(?string $hex): string
    {
        if (is_string($hex) && preg_match('/^#[0-9a-fA-F]{3}$/', $hex) === 1) {
            $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
        }
        if (!self::validCardHex($hex)) {
            return '#ffffff';
        }

        $lin = function (string $pair): float {
            $c = hexdec($pair) / 255;
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        $l = 0.2126 * $lin(substr($hex, 1, 2)) + 0.7152 * $lin(substr($hex, 3, 2)) + 0.0722 * $lin(substr($hex, 5, 2));

        // contrast with white = 1.05 / (L + 0.05); with black = (L + 0.05) / 0.05
        return (($l + 0.05) / 0.05) > (1.05 / ($l + 0.05)) ? '#000000' : '#ffffff';
    }

    public function getSafeHeaderColorAttribute(): string
    {
        return self::validCardHex($this->header_color) ? $this->header_color : self::CARD_DEFAULT_MAIN;
    }

    public function getSafeAccentColorAttribute(): string
    {
        return self::validCardHex($this->accent_color) ? $this->accent_color : self::CARD_DEFAULT_ACCENT;
    }

    public function getHeaderTextColorAttribute(): string
    {
        return self::contrastText($this->safe_header_color);
    }
}
