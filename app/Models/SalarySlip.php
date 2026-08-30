<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    protected $fillable = [
        'user_id', 'month', 'basic_salary', 'allowance', 'deduction',
        'manual_deduction', 'advance_deduction', 'net_salary',
        'payment_date', 'status', 'generated_by', 'paid_by',
        'total_days', 'present_days', 'absent_days', 'half_days',
        'leave_days', 'per_day_rate', 'attendance_deduction',
    ];

    protected $casts = [
        'basic_salary'         => 'decimal:2',
        'allowance'            => 'decimal:2',
        'deduction'            => 'decimal:2',
        'manual_deduction'     => 'decimal:2',
        'advance_deduction'    => 'decimal:2',
        'net_salary'           => 'decimal:2',
        'per_day_rate'         => 'decimal:2',
        'attendance_deduction' => 'decimal:2',
        'payment_date'         => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}