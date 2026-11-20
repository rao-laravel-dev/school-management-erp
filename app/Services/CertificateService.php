<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\SiteSetting;
use Carbon\Carbon;

// Certificates: types (built-in + custom), [bracket] placeholders, serial number
class CertificateService
{
    // Khali value print par saaf nazar aaye
    public const BLANK = '________';

    /**
     * Sab types (key => type array). Built-in types + site_settings.certificate_types:
     * ['overrides' => [key => [title, body, active]], 'custom' => [key => [title, prefix, fields, required, body, active]]]
     */
    public static function types(): array
    {
        $saved = SiteSetting::current()->certificate_types ?: [];
        $overrides = $saved['overrides'] ?? [];
        $custom = $saved['custom'] ?? [];

        $types = [];
        foreach (Certificate::TYPES as $key => $t) {
            $o = $overrides[$key] ?? [];
            $types[$key] = [
                'key' => $key,
                'title' => ($o['title'] ?? '') !== '' ? $o['title'] : $t['title'],
                'prefix' => $t['prefix'],
                'fields' => $t['fields'],
                'required' => $t['required'],
                'body' => ($o['body'] ?? '') !== '' ? $o['body'] : $t['body'],
                'active' => (bool) ($o['active'] ?? true),
                'custom' => false,
                'edited' => isset($overrides[$key]), // Certificate Types page: "Edited" badge + Reset button
            ];
        }
        foreach ($custom as $key => $t) {
            $types[$key] = [
                'key' => $key,
                'title' => $t['title'] ?? $key,
                'prefix' => $t['prefix'] ?? 'CRT',
                'fields' => array_values(array_intersect($t['fields'] ?? [], array_keys(Certificate::FIELDS))),
                'required' => array_values(array_intersect($t['required'] ?? [], array_keys(Certificate::FIELDS))),
                'body' => $t['body'] ?? '',
                'active' => (bool) ($t['active'] ?? true),
                'custom' => true,
                'edited' => false,
            ];
        }

        return $types;
    }

    public static function activeTypes(): array
    {
        return array_filter(self::types(), fn ($t) => $t['active']);
    }

    public static function type(string $key): ?array
    {
        return self::types()[$key] ?? null;
    }

    /**
     * [token] => value for one enrollment. $fields = per-certificate inputs (leaving_date, reason ...)
     */
    public static function placeholders(Enrollment $enrollment, array $fields, Carbon $issueDate, string $certificateNo = ''): array
    {
        $student = $enrollment->student;
        $parent = $student ? $student->parent : null;
        $dob = ($student && $student->date_of_birth) ? Carbon::parse($student->date_of_birth) : null;
        $admission = ($student && $student->admission_date) ? Carbon::parse($student->admission_date) : null;

        $gender = $student ? $student->gender : null;
        [$sonDaughter, $hisHer, $heShe, $himHer] = match ($gender) {
            'male' => ['son', 'his', 'he', 'him'],
            'female' => ['daughter', 'her', 'she', 'her'],
            default => ['son/daughter', 'his/her', 'he/she', 'him/her'],
        };

        $blank = fn ($v) => (trim((string) $v) === '') ? self::BLANK : trim((string) $v);

        $tokens = [
            '[student_name]' => $blank($student ? $student->full_name : ''),
            '[name]' => $blank($student ? $student->full_name : ''),
            '[father_name]' => $blank($parent ? $parent->father_name : ''),
            '[mother_name]' => $blank($parent ? $parent->mother_name : ''),
            '[gr_no]' => $blank($student ? $student->admission_no : ''),
            '[admission_no]' => $blank($student ? $student->admission_no : ''),
            '[dob]' => $dob ? $dob->format('d-m-Y') : self::BLANK,
            '[dob_words]' => $dob ? self::dateInWords($dob) : self::BLANK,
            '[admission_date]' => $admission ? $admission->format('d-m-Y') : self::BLANK,
            '[class]' => $blank(optional($enrollment->schoolClass)->name),
            '[section]' => $blank(optional($enrollment->section)->name),
            '[roll_no]' => $blank($enrollment->roll_no),
            '[session]' => $blank(optional($enrollment->academicYear)->name),
            '[son_daughter]' => $sonDaughter,
            '[his_her]' => $hisHer,
            '[he_she]' => $heShe,
            '[him_her]' => $himHer,
            '[His_Her]' => ucfirst($hisHer),
            '[He_She]' => ucfirst($heShe),
            '[school_name]' => $blank(SiteSetting::current()->school_name),
            '[issue_date]' => $issueDate->format('d-m-Y'),
            '[certificate_no]' => $certificateNo !== '' ? $certificateNo : self::BLANK,
        ];

        foreach (Certificate::FIELDS as $key => $meta) {
            $v = $fields[$key] ?? '';
            if ($key === 'leaving_date' && trim((string) $v) !== '') {
                $v = Carbon::parse($v)->format('d-m-Y');
            }
            $tokens['[' . $key . ']'] = $blank($v);
        }

        return $tokens;
    }

    // [token] -> value (strtr: ek hi pass, replaced text dobara replace nahi hota)
    public static function render(string $body, array $tokens): string
    {
        return strtr($body, $tokens);
    }

    /**
     * Agla serial. Sirf DB::transaction ke andar bulao: type+saal ki rows lock hoti hain.
     * @return array [serial, certificate_no]
     */
    public static function nextSerial(string $typeKey, string $prefix, int $year): array
    {
        $serial = (int) Certificate::where('type', $typeKey)->where('serial_year', $year)->lockForUpdate()->max('serial') + 1;

        return [$serial, sprintf('%s-%d-%04d', $prefix, $year, $serial)];
    }

    // 12-03-2015 -> "Twelfth March Two Thousand Fifteen"
    public static function dateInWords(Carbon $date): string
    {
        return self::ordinalWords($date->day) . ' ' . $date->format('F') . ' ' . self::numberWords($date->year);
    }

    private static function ordinalWords(int $n): string
    {
        $ord = [1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth', 5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh',
            8 => 'Eighth', 9 => 'Ninth', 10 => 'Tenth', 11 => 'Eleventh', 12 => 'Twelfth', 13 => 'Thirteenth',
            14 => 'Fourteenth', 15 => 'Fifteenth', 16 => 'Sixteenth', 17 => 'Seventeenth', 18 => 'Eighteenth',
            19 => 'Nineteenth', 20 => 'Twentieth', 30 => 'Thirtieth'];
        if (isset($ord[$n])) {
            return $ord[$n];
        }

        // 21-29, 31
        return self::numberWords($n - $n % 10) . ' ' . $ord[$n % 10];
    }

    // 0-9999 in words (date ke saal ke liye kaafi)
    private static function numberWords(int $n): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = [2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'];

        if ($n === 0) {
            return 'Zero';
        }
        $parts = [];
        if ($n >= 1000) {
            $parts[] = $ones[intdiv($n, 1000)] . ' Thousand';
            $n %= 1000;
        }
        if ($n >= 100) {
            $parts[] = $ones[intdiv($n, 100)] . ' Hundred';
            $n %= 100;
        }
        if ($n >= 20) {
            $parts[] = $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
        } elseif ($n > 0) {
            $parts[] = $ones[$n];
        }

        return implode(' ', $parts);
    }
}
