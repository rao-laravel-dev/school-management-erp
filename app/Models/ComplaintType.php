<?php

namespace App\Models; // Agar nested folder me rkhna ho

use Illuminate\Database\Eloquent\Model;
use App\Models\Complaint;

class ComplaintType extends Model
{
    protected $fillable = ['name', 'description'];

    // One-to-Many Relationship with Complaint
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'complaint_type_id');
    }
}
