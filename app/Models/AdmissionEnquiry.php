<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Source;
use App\Models\Purpose;
use App\Models\User;

class AdmissionEnquiry extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'description',
        'date',
        'next_follow_up_date',
        'source_id',
        'purpose_id',
        'assigned_to',
        'status'
    ];

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
