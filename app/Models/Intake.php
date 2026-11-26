<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intake extends Model
{
    protected $fillable = [
        'name', 'code', 'academic_year', 'start_date', 'end_date', 'capacity', 'is_active', 'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'academic_year' => 'integer',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function studentCourses(): HasMany
    {
        return $this->hasMany(StudentCourse::class);
    }
}
