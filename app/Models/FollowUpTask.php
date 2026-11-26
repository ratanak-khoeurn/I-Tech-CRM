<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpTask extends Model
{
    use StudentCampusScoped;
    protected $fillable = [
        'student_id', 'assigned_to', 'created_by', 'communication_log_id', 'title', 'description',
        'priority', 'due_date', 'reminder_enabled', 'reminder_at', 'status', 'result', 'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_enabled' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function communicationLog(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class);
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['pending', 'in_progress'])
            && $this->due_date->isPast();
    }
}
