<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $fillable = ['owner_type', 'owner_id', 'code'];

    public function owner()
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }
}
