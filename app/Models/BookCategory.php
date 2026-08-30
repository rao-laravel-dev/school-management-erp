<?php

namespace App\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    protected $fillable = ['name', 'description', 'status'];

    public function books()
    {
        return $this->hasMany(Book::class, 'book_category_id');
    }
}
