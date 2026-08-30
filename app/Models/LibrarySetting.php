<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $fillable = [
        'max_issue_days',
        'fine_per_day',
        'max_books_per_student',
        'max_books_per_staff',
    ];
}
