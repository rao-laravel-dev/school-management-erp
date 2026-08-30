<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffBankDetail extends Model
{
    protected $fillable = [
        'user_id', 'bank_name', 'bank_type', 'account_title', 
        'account_number', 'iban', 'branch_code', 'branch_name'
    ];

    public function setAccountTitleAttribute($value)
    {
        $this->attributes['account_title'] = ucwords(strtolower($value));
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
