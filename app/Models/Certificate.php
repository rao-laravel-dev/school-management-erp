<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'certificate_no', 'type', 'serial_year', 'serial', 'student_id', 'enrollment_id', 'title', 'body',
        'fields', 'design', 'orientation', 'issue_date', 'status', 'cancel_reason', 'cancelled_at',
        'issued_by', 'cancelled_by',
    ];

    protected $casts = [
        'fields' => 'array',
        'issue_date' => 'date',
        'cancelled_at' => 'datetime',
        'serial' => 'integer',
        'serial_year' => 'integer',
    ];

    public const STATUS_ISSUED = 'issued';
    public const STATUS_CANCELLED = 'cancelled';

    public const ORIENTATIONS = ['portrait' => 'Portrait', 'landscape' => 'Landscape'];
    // old: public const DESIGNS = ['classic' => 'Classic'];
    public const DESIGNS = [
        'classic' => 'Classic (double border)',
        'elegant' => 'Elegant (ornate corners)',
        'modern'  => 'Modern (colour band)',
        'formal'  => 'Formal (plain, official)',
        'royal'   => 'Royal (gold frame)',
    ];

    // Per-certificate fields: key => [label, input type, max length]. Type ke 'fields' / 'required' inhi keys ko use karte hain
    public const FIELDS = [
        'leaving_date' => ['Leaving Date', 'date', 0],
        'reason'       => ['Reason for Leaving', 'text', 150],
        'conduct'      => ['Conduct', 'text', 50],
        'exam'         => ['Exam', 'text', 100],
        'result'       => ['Result', 'text', 100],
        'purpose'      => ['Purpose', 'text', 150],
        'event'        => ['Event / Achievement', 'text', 150],
        'position'     => ['Position', 'text', 100],
        'remarks'      => ['Remarks', 'text', 255],
    ];

    // Conduct ke suggestions (datalist), admin kuch bhi likh sakta hai
    public const CONDUCT_OPTIONS = ['Excellent', 'Very Good', 'Good', 'Satisfactory'];

    // Class names already contain the word "Class", so the wording says [class] only. Old fragments:
    // old character:    '... is a student of class [class] during session [session]. ...'
    // old hope:         '... is a regular student of class [class]. ...'
    // old bonafide:     '... is a bonafide student of class [class] (section [section]) ...'
    // old no_dues:      '... GR No. [gr_no], class [class], has cleared all dues ...'
    // old appreciation: '... of [father_name], class [class], in appreciation of ...'
    // old sports:       '... of [father_name], class [class], participated in ...'
    // Built-in types: key => [title, prefix, fields, required fields, default wording]. Wording site_settings.certificate_types se override ho sakta hai
    public const TYPES = [
        'school_leaving' => [
            'title' => 'School Leaving / Transfer Certificate',
            'prefix' => 'SLC',
            'fields' => ['leaving_date', 'reason', 'conduct', 'remarks'],
            'required' => ['leaving_date', 'reason'],
            // old: 'body' => '... [He_She] was studying in class [class] during session [session]. ...' (class names already contain the word "Class")
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], GR No. [gr_no], was a bonafide student of this school from [admission_date] to [leaving_date]. [He_She] was studying in [class] during session [session]. [His_Her] date of birth as per school record is [dob] ([dob_words]). [He_She] has left the school due to [reason]. [His_Her] conduct during the stay was [conduct]. We wish [him_her] success in life.',
        ],
        'character' => [
            'title' => 'Character Certificate',
            'prefix' => 'CHR',
            'fields' => ['conduct', 'remarks'],
            'required' => ['conduct'],
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], GR No. [gr_no], is a student of [class] during session [session]. To the best of my knowledge [he_she] bears a good moral character and [his_her] conduct has been [conduct].',
        ],
        'provisional' => [
            'title' => 'Provisional Certificate',
            'prefix' => 'PRV',
            'fields' => ['exam', 'result'],
            'required' => ['exam', 'result'],
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], GR No. [gr_no], has appeared in the [exam] held in session [session] and has [result]. This certificate is issued provisionally until the original result card is issued.',
        ],
        'hope' => [
            'title' => 'Hope Certificate',
            'prefix' => 'HOP',
            'fields' => ['exam'],
            'required' => ['exam'],
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], GR No. [gr_no], is a regular student of [class]. [He_She] is appearing in the [exam]. [He_She] is a hardworking student and we hope [he_she] will pass with good marks.',
        ],
        'bonafide' => [
            'title' => 'Bonafide Certificate',
            'prefix' => 'BON',
            'fields' => ['purpose'],
            'required' => [],
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], GR No. [gr_no], is a bonafide student of [class] (section [section]) of this school for session [session]. [His_Her] date of birth as per school record is [dob]. This certificate is issued on request for [purpose].',
        ],
        'date_of_birth' => [
            'title' => 'Date of Birth Certificate',
            'prefix' => 'DOB',
            'fields' => ['purpose'],
            'required' => [],
            'body' => 'This is to certify that, according to the admission register of this school, the date of birth of [student_name] [son_daughter] of [father_name], GR No. [gr_no], is [dob] ([dob_words]).',
        ],
        'no_dues' => [
            'title' => 'No Dues Certificate',
            'prefix' => 'NDC',
            'fields' => ['remarks'],
            'required' => [],
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], GR No. [gr_no], [class], has cleared all dues of the school up to [issue_date]. Nothing is outstanding against [him_her].',
        ],
        'appreciation' => [
            'title' => 'Certificate of Appreciation',
            'prefix' => 'APR',
            'fields' => ['event'],
            'required' => ['event'],
            'body' => 'This certificate is proudly presented to [student_name] [son_daughter] of [father_name], [class], in appreciation of [his_her] [event] during session [session].',
        ],
        'sports' => [
            'title' => 'Sports Certificate',
            'prefix' => 'SPT',
            'fields' => ['event', 'position'],
            'required' => ['event', 'position'],
            'body' => 'This is to certify that [student_name] [son_daughter] of [father_name], [class], participated in [event] and secured [position]. We congratulate [him_her] on this achievement.',
        ],
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
