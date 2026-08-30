<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinePolicy extends Model
{
    protected $fillable = [
        'name',
        'fee_type_id',
        'grace_days',
        'fine_type',
        'fine_value',
        'max_fine',
        'status',
    ];

    protected $casts = [
        'fine_value' => 'decimal:2',
        'max_fine'   => 'decimal:2',
        'status'     => 'boolean',
    ];

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function studentFeeFines()
    {
        return $this->hasMany(StudentFeeFine::class);
    }
}