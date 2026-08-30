<?php

namespace App\Models;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
     protected $fillable = ['name'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
