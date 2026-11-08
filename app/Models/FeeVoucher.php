<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeVoucher extends Model
{
    protected $fillable = [
        'voucher_no',
        'student_id',
        'academic_year_id',
        'period',
        'student_fee_ids',
        'issue_date',
        'due_date',
        'fee_amount',
        'discount',
        'arrears',
        'late_fee',
        'amount_before_due',
        'amount_after_due',
        'bank_account_id',
        'status',
        'paid_at',
        'consumer_no',
        'onebill_invoice_no',
        'bank_reference',
        'created_by',
    ];

    protected $casts = [
        'student_fee_ids'   => 'array',
        'issue_date'        => 'date',
        'due_date'          => 'date',
        'fee_amount'        => 'decimal:2',
        'discount'          => 'decimal:2',
        'arrears'           => 'decimal:2',
        'late_fee'          => 'decimal:2',
        'amount_before_due' => 'decimal:2',
        'amount_after_due'  => 'decimal:2',
        'paid_at'           => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
