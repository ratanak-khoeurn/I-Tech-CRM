<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassAttendance extends Model
{
    use CampusScoped;

    public const STATUSES = [
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'excused' => 'Excused',
    ];

    /** Badge colours used by the register and any later report. */
    public const STATUS_CLASSES = [
        'present' => 'success',
        'absent' => 'danger',
        'late' => 'warning',
        'excused' => 'secondary',
    ];

    protected $fillable = [
        'study_group_id', 'student_id', 'campus_id', 'attendance_date', 'status', 'note', 'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
