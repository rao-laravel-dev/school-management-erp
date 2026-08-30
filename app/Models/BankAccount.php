<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name',
        'bank_type',
        'account_title',
        'account_number',
        'iban',
        'branch_name',
        'branch_code',
        'current_balance'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
