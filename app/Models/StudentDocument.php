<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    use StudentCampusScoped;
    public const TYPES = [
        'photo' => 'Student Photo',
        'certificate' => 'Certificate',
        'cv' => 'CV / Resume',
        'transcript' => 'Academic Transcript',
        'internship_doc' => 'Internship Document',
        'employment_doc' => 'Employment Document',
        'other' => 'Other',
    ];

    protected $fillable = [
        'student_id', 'name', 'doc_type', 'file_path', 'file_size', 'description',
        'is_sensitive', 'uploaded_by',
    ];

    protected $casts = ['is_sensitive' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
