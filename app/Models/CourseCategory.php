<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_en',
        'name_kh',
        'program_id',
        'sort_order',
        'name_import',
        'semester_id',
        'academic_year_id',
        'extra_course',
        'penalty',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'semester_id' => 'integer',
        'academic_year_id' => 'integer',
        'sort_order' => 'integer',
        'extra_course' => 'boolean',
        'penalty' => 'boolean',
    ];

    /**
     * Course Category has many Courses.
     */
    public function courses()
    {
        return $this->hasMany(
            Course::class,
            'category_id'
        );
    }

    /**
     * Course Category belongs to a Program.
     */
    public function program()
    {
        return $this->belongsTo(
            Program::class,
            'program_id'
        );
    }

    /**
     * Course Category belongs to a Semester.
     */
    public function semester()
    {
        return $this->belongsTo(
            Semester::class,
            'semester_id'
        );
    }

    /**
     * Course Category belongs to an Academic Year.
     *
     * course_categories.academic_year_id
     * references
     * academic_years.id
     */
    public function academicYear()
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }

    /**
     * Course Category has many Academic Years.
     *
     * academic_years.course_category_id
     * references
     * course_categories.id
     */
    public function academicYears()
    {
        return $this->hasMany(
            AcademicYear::class,
            'course_category_id'
        );
    }
}
