<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BillingSetting extends Model
{
    protected $fillable = [
        'academic_year_id',
        'fee_generate_day',
        'fee_due_day',
        'salary_generate_day',
        'auto_fees',
        'auto_salary',
        'fees_from_month',
        'salary_from_month',
        'late_fee_amount',
        'voucher_bank_account_id',
        'voucher_note',
        'scholarship_category_id',
        'onebill_enabled',
        'onebill_bank',
        'onebill_biller_code',
        'onebill_username',
        'onebill_password',
        'onebill_api_key',
    ];

    // 1Bill secrets APP_KEY se encrypted hain: kabhi toArray()/JSON mein decrypt na hon
    public const ONEBILL_SECRETS = ['onebill_username', 'onebill_password', 'onebill_api_key'];

    protected $hidden = self::ONEBILL_SECRETS;

    protected $casts = [
        'auto_fees'        => 'boolean',
        'auto_salary'      => 'boolean',
        'late_fee_amount'  => 'decimal:2',
        'onebill_enabled'  => 'boolean',
        'onebill_username' => 'encrypted',
        'onebill_password' => 'encrypted',
        'onebill_api_key'  => 'encrypted',
    ];

    /**
     * 1Bill secret ki halat: empty / saved / unreadable (dusri site ki APP_KEY se likhi value).
     * Sirf DecryptException pakri jati hai: local aur live keys alag hone par ye expected hai.
     */
    public function credentialStatus(string $field): string
    {
        $raw = $this->getRawOriginal($field);
        if ($raw === null || $raw === '') {
            return 'empty';
        }

        try {
            Crypt::decryptString($raw);
            return 'saved';
        } catch (DecryptException $e) {
            return 'unreadable';
        }
    }

    /**
     * Nayi secret value set karo. Purani value unreadable ho to original null kar do,
     * warna Eloquent dirty check purani value decrypt karte hue crash karta hai.
     */
    public function replaceCredential(string $field, string $value): void
    {
        if ($this->credentialStatus($field) === 'unreadable') {
            $this->original[$field] = null;
        }

        $this->{$field} = $value;
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'voucher_bank_account_id');
    }

    public function scholarshipCategory()
    {
        return $this->belongsTo(StudentCategory::class, 'scholarship_category_id');
    }
}
