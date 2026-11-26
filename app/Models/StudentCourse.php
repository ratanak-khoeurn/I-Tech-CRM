<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourse extends Model
{
    // Enrollments follow the student they belong to: a record with no campus written
    // on it still has to show up on that student's page for the owning campus account.
    use StudentCampusScoped;

    public const STATUS_LABELS = [
        'in_progress' => 'Current',
        'completed' => 'Completed',
        'deferred' => 'Deferred',
        'withdrawn' => 'Withdrawn',
    ];

    public const STATUS_BADGE_COLORS = [
        'in_progress' => 'primary',
        'completed' => 'success',
        'deferred' => 'warning',
        'withdrawn' => 'danger',
    ];

    protected $fillable = [
        'student_id', 'campus_id', 'course_id', 'enrollment_status', 'batch_id', 'intake_id', 'semester_id',
        'study_group_id',
        'center_name', 'registration_number', 'major',
        'class_group', 'instructor_name', 'enrollment_date', 'course_start_date',
        'course_completion_date', 'graduation_date', 'graduation_year', 'attendance_percentage',
        'grade', 'certificate_received', 'certificate_number', 'scholarship_status',
        'internship_participation', 'internship_company', 'graduation_project',
        'withdrawal_reason', 'alumni_membership_status', 'alumni_membership_date', 'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'course_start_date' => 'date',
        'course_completion_date' => 'date',
        'graduation_date' => 'date',
        'alumni_membership_date' => 'date',
        'certificate_received' => 'boolean',
        'internship_participation' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUS_LABELS[$this->enrollment_status] ?? 'Current');
    }

    public function statusBadgeClass(): Attribute
    {
        return Attribute::get(fn () => 'bg-' . (self::STATUS_BADGE_COLORS[$this->enrollment_status] ?? 'primary'));
    }

    public function isCurrent(): bool
    {
        return in_array($this->enrollment_status, ['in_progress', 'deferred'], true);
    }
}
