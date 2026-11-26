<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentHistory extends Model
{
    use StudentCampusScoped;
    protected $table = 'employment_histories';

    protected $fillable = [
        'student_id', 'is_current', 'company', 'job_title', 'industry', 'department',
        'company_address', 'work_phone', 'work_email', 'start_date', 'end_date', 'salary_range',
        'employment_type', 'employment_status', 'description',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
