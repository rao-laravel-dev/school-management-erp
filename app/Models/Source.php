<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdmissionEnquiry;

class Source extends Model
{
    protected $fillable = ['name', 'description'];

    public function enquiries()
    {
        return $this->hasMany(AdmissionEnquiry::class, 'source_id');
    }
}
