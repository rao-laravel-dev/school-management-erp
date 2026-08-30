<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHouse extends Model
{
    protected $table = 'student_houses';
    
    protected $fillable = ['name', 'color', 'status'];

    public function students()
    {
        return $this->hasMany(Student::class, 'house_id');
    }
}
    