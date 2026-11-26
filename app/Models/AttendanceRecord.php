<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use CampusScoped;
    protected $fillable = [
        'student_id',
        'employee_id',
        'campus_id',
        'attendance_date',
        'check_in_at',
        'checked_in_by',
        'check_out_at',
        'checked_out_by',
        'remark',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function personName(): string
    {
        if ($this->student) {
            return $this->student->name_english ?: $this->student->name_khmer ?: 'Unknown student';
        }

        return $this->employee?->fullName() ?? 'Unknown employee';
    }

    public function personCode(): ?string
    {
        return $this->student?->student_code ?? $this->employee?->employee_code;
    }

    public function personTypeLabel(): string
    {
        return $this->student_id ? 'Student' : 'Employee';
    }
}
