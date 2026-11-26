<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'program_id', 'category_id', 'course_category', 'description', 'certificate_text', 'duration_months', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function studentCourses()
    {
        return $this->hasMany(StudentCourse::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function isExtraCourse(): bool
    {
        return (bool) $this->category?->extra_course;
    }
}
