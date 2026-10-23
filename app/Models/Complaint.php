<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ComplaintType;
use App\Models\Source;
use App\Models\User;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_by',
        'phone',
        'email',
        'date',
        'description',
        'action_taken',
        'complaint_type_id',
        'source_id',
        'assigned_to',
        'document',
        'status',
        'created_by',
        'updated_by'
    ];

    public function complaintType()
    {
        return $this->belongsTo(ComplaintType::class, 'complaint_type_id');
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    public function staff()
    {
        // withTrashed: Edit mein saved staff (ab delete ho gaya ho) bhi dikhe
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}