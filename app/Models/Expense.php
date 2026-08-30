<?php

namespace App\Models;

use App\Models\BankAccount;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id', 'bank_account_id', 'created_by',
        'title', 'description', 'amount', 'payment_method',
        'expense_date', 'status',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
