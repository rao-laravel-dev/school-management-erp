<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    protected $fillable = [
        'name',
        'result_scope',
    ];

    // Display mein hamesha Title Case
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? ucwords($value) : $value,
        );
    }

    public function isCumulative(): bool
    {
        return $this->result_scope === 'cumulative';
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
