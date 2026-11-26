<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachLead extends Model
{
    use StudentCampusScoped;
    public const STAGE_TO_CONTACT = 'to_contact';
    public const STAGE_CONTACTED = 'contacted';

    public const STAGES = [
        self::STAGE_TO_CONTACT => 'To Contact',
        self::STAGE_CONTACTED => 'Contacted',
        'no_answer' => 'No Answer',
        'interested' => 'Interested',
        'registered' => 'Registered',
        'declined' => 'Declined',
    ];

    public const STAGE_COLORS = [
        self::STAGE_TO_CONTACT => 'secondary',
        'contacted' => 'info',
        'no_answer' => 'dark',
        'interested' => 'warning',
        'registered' => 'success',
        'declined' => 'danger',
    ];

    /** Stages the event-registration sync is allowed to overwrite. */
    private const SYNCABLE_STAGES = [self::STAGE_TO_CONTACT, 'contacted', 'no_answer', 'interested'];

    protected $fillable = [
        'outreach_campaign_id', 'student_id', 'stage', 'channel', 'note', 'contacted_at', 'created_by',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OutreachCampaign::class, 'outreach_campaign_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public static function stageLabel(?string $stage): string
    {
        return self::STAGES[$stage] ?? self::STAGES[self::STAGE_TO_CONTACT];
    }

    /**
     * Pull registered people from the linked event's registration list.
     *
     * Someone who explicitly declined stays declined — only the earlier stages
     * are advanced, so a human decision is never overwritten by the sync.
     */
    public static function syncRegisteredFromEvent(OutreachCampaign $campaign): int
    {
        if ($campaign->alumni_event_id === null) {
            return 0;
        }

        $registeredIds = EventRegistration::where('alumni_event_id', $campaign->alumni_event_id)
            ->whereIn('status', ['registered', 'attended'])
            ->pluck('student_id')
            ->all();

        if ($registeredIds === []) {
            return 0;
        }

        return self::query()
            ->where('outreach_campaign_id', $campaign->id)
            ->whereIn('stage', self::SYNCABLE_STAGES)
            ->whereIn('student_id', $registeredIds)
            ->update([
                'stage' => 'registered',
                'updated_at' => now(),
            ]);
    }
}
