<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutreachCampaign extends Model
{
    use SoftDeletes;

    public const PURPOSES = [
        'event' => 'Event invitation',
        'opportunity' => 'Job / internship opportunity',
        'survey' => 'Survey / feedback',
        'announcement' => 'Announcement',
        'reunion' => 'Reunion',
        'other' => 'Other outreach',
    ];

    public const AUDIENCE_FLAGS = [
        'interested_activities' => 'Interested in activities',
        'interested_job_announcements' => 'Wants job announcements',
        'interested_training' => 'Wants training',
        'interested_volunteering' => 'Open to volunteering',
        'interested_mentoring' => 'Open to mentoring',
        'interested_internships' => 'Seeking internships',
        'interested_newsletter' => 'Subscribed to newsletter',
        'is_seeking_employment' => 'Currently seeking employment',
    ];

    protected $fillable = [
        'name', 'purpose', 'alumni_event_id', 'description', 'audience_filters', 'status', 'created_by',
    ];

    protected $casts = [
        'audience_filters' => 'array',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(OutreachLead::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AlumniEvent::class, 'alumni_event_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purposeLabel(): string
    {
        return self::PURPOSES[$this->purpose] ?? ucfirst(str_replace('_', ' ', (string) $this->purpose));
    }

    /** How far through the campaign the list is, as a percentage of decided contacts. */
    public function progressPercentage(): int
    {
        $total = $this->leads()->count();
        if ($total === 0) {
            return 0;
        }
        $pending = $this->leads()->where('stage', OutreachLead::STAGE_TO_CONTACT)->count();

        return (int) round(($total - $pending) / $total * 100);
    }
}
