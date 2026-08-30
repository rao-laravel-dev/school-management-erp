<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFees extends Model
{
    protected $fillable = [
        'fee_structure_id',
        'student_id',
        'fee_type_id',
        'amount',
        'discount',      // 🔥 add
        'paid_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'discount'    => 'decimal:2',   // 🔥 add
        'paid_amount' => 'decimal:2',
        'due_date'    => 'date',
        'status'      => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function fines()
    {
        return $this->hasMany(StudentFeeFine::class, 'student_fee_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'student_fee_id'); // apne actual FK name se match karo
    }
}
