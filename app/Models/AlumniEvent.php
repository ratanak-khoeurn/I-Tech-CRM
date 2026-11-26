<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumniEvent extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'reunion' => 'Alumni Reunion',
        'workshop' => 'Workshop',
        'career_talk' => 'Career Talk',
        'networking' => 'Networking Event',
        'training' => 'Training Session',
        'volunteer' => 'Volunteer Activity',
        'mentorship' => 'Mentorship Program',
    ];

    protected $fillable = [
        'title', 'description', 'event_type', 'start_datetime', 'end_datetime', 'location',
        'registration_deadline', 'max_participants', 'organizer', 'target_group', 'feedback',
        'photo', 'status', 'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'registration_deadline' => 'date',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function seatsLeft(): ?int
    {
        if ($this->max_participants === null) {
            return null;
        }
        return max(0, $this->max_participants - $this->registrations()->whereIn('status', ['registered', 'attended'])->count());
    }
}
