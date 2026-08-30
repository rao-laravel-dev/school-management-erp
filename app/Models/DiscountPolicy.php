<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountPolicy extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'policy_type',
        'trigger_value',
        'discount_type',
        'discount_value',
        'fee_type_id',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'status'         => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(StudentCategory::class, 'category_id');
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function studentDiscounts()
    {
        return $this->hasMany(StudentDiscount::class);
    }
}