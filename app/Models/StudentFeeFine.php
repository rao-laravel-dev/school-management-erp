<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeFine extends Model
{
    protected $fillable = [
        'student_fee_id',
        'fine_policy_id',
        'calculated_fine',
        'applied_fine',
        'reason',
        'applied_by',
        'transaction_id',
    ];

    protected $casts = [
        'calculated_fine' => 'decimal:2',
        'applied_fine'    => 'decimal:2',
    ];

    public function studentFee()
    {
        return $this->belongsTo(StudentFees::class, 'student_fee_id');
    }

    public function finePolicy()
    {
        return $this->belongsTo(FinePolicy::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}