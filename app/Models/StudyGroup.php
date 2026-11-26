<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudyGroup extends Model
{
    use CampusScoped;
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'campus_id',
        'course_category_id',
        'academic_year_id',
        'program_id',
        'intake_id',
        'teacher_id',
        'study_time',
        'all_courses',
        'graduation_year',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'all_courses' => 'boolean',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_study_group')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'study_group_student')
            ->withTimestamps();
    }
}
