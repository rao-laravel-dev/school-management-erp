<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'auto_fees'        => 'boolean',
        'auto_salary'      => 'boolean',
        'late_fee_amount'  => 'decimal:2',
        'onebill_enabled'  => 'boolean',
        'onebill_username' => 'encrypted',
        'onebill_password' => 'encrypted',
        'onebill_api_key'  => 'encrypted',
    ];

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
