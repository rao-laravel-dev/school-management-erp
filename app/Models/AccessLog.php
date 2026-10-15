<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLog extends Model
{
    // Sirf created_at (audit row kabhi update nahi hoti)
    const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'target_type',
        'target_id',
        'target_name',
        'action',
        'old_value',
        'new_value',
        'ip',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }
}
