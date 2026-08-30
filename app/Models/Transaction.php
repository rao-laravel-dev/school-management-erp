<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'user_id',
        'student_fee_id',
        'salary_slip_id',
        'expense_id',
        'payment_group_id',
        'type',
        'category',
        'amount',
        'discount',        // 🔥 add
        'fine',            // 🔥 add
        'payment_method',
        'reference_no',
        'transaction_date',
        'note',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'discount'         => 'decimal:2',   // 🔥 add
        'fine'             => 'decimal:2',   // 🔥 add
        'transaction_date' => 'date',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentFee()
    {
        return $this->belongsTo(StudentFees::class, 'student_fee_id');
    }

    public function salarySlip()
    {
        return $this->belongsTo(SalarySlip::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }


}