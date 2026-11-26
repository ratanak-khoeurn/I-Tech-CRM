<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use StudentCampusScoped;
    protected $fillable = [
        'alumni_event_id',
        'student_id',
        'status',
        'attended_at',
        'confirmation_sent_at',
        'feedback',
        'registered_by',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
        'confirmation_sent_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(AlumniEvent::class, 'alumni_event_id');
    }

    /**
     * Whether the registrant has been checked in as attending.
     */
    public function isCheckedIn(): bool
    {
        return $this->status === 'attended';
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
