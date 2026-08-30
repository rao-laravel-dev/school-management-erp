<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'frequency',
        'is_discountable',
    ];

    // Relationship (destroy() method mein already use ho raha hai)
    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function discountPolicies()
    {
        return $this->hasMany(DiscountPolicy::class);
    }

    public function finePolicies()
    {
        return $this->hasMany(FinePolicy::class);
    }
}
