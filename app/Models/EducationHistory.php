<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationHistory extends Model
{
    use StudentCampusScoped;
    protected $table = 'education_histories';

    protected $fillable = [
        'student_id', 'is_current', 'institution', 'degree_program', 'major', 'year_level',
        'start_date', 'expected_graduation_date', 'scholarship_info', 'further_education_plans',
        'status', 'notes',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'expected_graduation_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
