<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationLog extends Model
{
    use StudentCampusScoped;
    public const CHANNELS = [
        'phone_call' => 'Phone Call',
        'telegram' => 'Telegram Message',
        'email' => 'Email',
        'sms' => 'SMS',
        'facebook' => 'Facebook Message',
        'in_person' => 'In-person Meeting',
        'alumni_event' => 'Alumni Event',
        'other' => 'Other',
    ];

    protected $fillable = [
        'student_id', 'user_id', 'contact_datetime', 'channel', 'direction', 'subject',
        'message_summary', 'outcome', 'followup_required', 'next_followup_date', 'attachment',
        'internal_notes',
    ];

    protected $casts = [
        'contact_datetime' => 'datetime',
        'next_followup_date' => 'date',
        'followup_required' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followUpTasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }
}
