<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use CampusScoped;
    public const EMPLOYMENT_TYPES = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'contract' => 'Contract',
        'intern' => 'Intern',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'on_leave' => 'On Leave',
        'resigned' => 'Resigned',
        'terminated' => 'Terminated',
    ];

    protected $fillable = [
        'employee_code', 'user_id', 'position_id', 'campus_id', 'first_name', 'last_name',
        'photo', 'gender', 'date_of_birth', 'email', 'phone', 'address', 'hire_date',
        'employment_type', 'status', 'salary', 'contract_end_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public static function generateEmployeeCode(): string
    {
        $year = Carbon::now()->format('Y');
        // employee_code is unique centre-wide, so the sequence ignores the campus filter.
        $last = static::withoutGlobalScopes()
            ->where('employee_code', 'like', "EMP-$year-%")
            ->orderByDesc('employee_code')
            ->value('employee_code');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return sprintf('EMP-%s-%04d', $year, $seq);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function studyGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class, 'teacher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function faceProfile(): HasOne
    {
        return $this->hasOne(EmployeeFaceProfile::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function employmentTypeLabel(): string
    {
        return self::EMPLOYMENT_TYPES[$this->employment_type] ?? ucfirst((string) $this->employment_type);
    }
}
