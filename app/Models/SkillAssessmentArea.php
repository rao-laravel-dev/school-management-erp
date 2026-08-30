<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillAssessmentArea extends Model
{
    protected $fillable = ['category_id', 'name', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(SkillCategory::class, 'category_id');
    }

    public function studentSkillMarks()
    {
        return $this->hasMany(StudentSkillMark::class, 'area_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
