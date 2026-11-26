<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use CampusScoped;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'from',
        'to',
        'campus_id',
        'course_category_id',
        'academic_year',
        'current_year',
    ];

    protected $casts = [
        'from' => 'date',
        'to' => 'date',
        'campus_id' => 'integer',
        'course_category_id' => 'integer',
        'current_year' => 'boolean',
    ];

    /**
     * Academic Year belongs to a Campus.
     */
    public function campus()
    {
        return $this->belongsTo(
            Campus::class,
            'campus_id'
        );
    }

    /**
     * Academic Year belongs to a Course Category.
     */
    public function courseCategory()
    {
        return $this->belongsTo(
            CourseCategory::class,
            'course_category_id'
        );
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }
}
