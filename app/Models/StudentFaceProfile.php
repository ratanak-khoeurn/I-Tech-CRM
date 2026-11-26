<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFaceProfile extends Model
{
    use StudentCampusScoped;
    protected $fillable = [
        'student_id',
        'embedding',
        'model_version',
        'is_active',
        'consent_obtained_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'encrypted:array',
            'is_active' => 'boolean',
            'consent_obtained_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
