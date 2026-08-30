<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookIssue extends Model
{
    protected $fillable = [
        'book_id',
        'library_member_id',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'fine_paid',
        'issued_by',
        'returned_by',
        'remarks',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function libraryMember()
    {
        return $this->belongsTo(LibraryMember::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
