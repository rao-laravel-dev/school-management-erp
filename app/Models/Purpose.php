<?php

namespace App\Models;

use App\Models\AdmissionEnquiry;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $fillable = ['name', 'description'];

    public function enquiries()
    {
        return $this->hasMany(AdmissionEnquiry::class, 'purpose_id');
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'purpose_id');
    }
}
