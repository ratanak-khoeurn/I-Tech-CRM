<?php

namespace App\Services;

use App\Models\OutreachCampaign;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Turns a campaign's saved audience filters into lead rows.
 *
 * Adding is additive on purpose: rebuilding the audience must never delete a
 * card someone already worked on, so existing leads keep their stage.
 */
class AudienceBuilder
{
    public const MAX_LEADS = 2000;

    public function queryFor(array $filters): Builder
    {
        $query = Student::query()
            ->where('record_status', 'active')
            // Outreach never targets a student the center asked us to leave alone.
            ->where('status', '!=', 'do_not_contact');

        foreach (['student_stage', 'graduation_status', 'engagement_status', 'status', 'gender', 'alumni_group'] as $column) {
            if (!empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        if (!empty($filters['course_id']) || !empty($filters['campus_id']) || !empty($filters['graduation_year'])) {
            $query->whereHas('studentCourses', function ($w) use ($filters) {
                foreach (['course_id', 'campus_id', 'graduation_year'] as $column) {
                    if (!empty($filters[$column])) {
                        $w->where($column, $filters[$column]);
                    }
                }
            });
        }

        foreach (array_keys(OutreachCampaign::AUDIENCE_FLAGS) as $flag) {
            if (!empty($filters['flags'][$flag])) {
                $query->where($flag, true);
            }
        }

        return $query->orderBy('name_english');
    }

    public function countFor(array $filters): int
    {
        return $this->queryFor($filters)->count();
    }

    /**
     * @return array{matched: int, added: int, already_present: int, capped: bool}
     */
    public function apply(OutreachCampaign $campaign, int $userId): array
    {
        $filters = $campaign->audience_filters ?? [];

        $matched = $this->queryFor($filters)
            ->limit(self::MAX_LEADS)
            ->pluck('students.id')
            ->all();

        $present = $campaign->leads()->pluck('student_id')->all();
        $missing = array_values(array_diff($matched, $present));

        if ($missing !== []) {
            $now = now();
            foreach (array_chunk($missing, 500) as $chunk) {
                DB::table('outreach_leads')->insert(array_map(fn (int $studentId) => [
                    'outreach_campaign_id' => $campaign->id,
                    'student_id' => $studentId,
                    'stage' => 'to_contact',
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));
            }
        }

        return [
            'matched' => count($matched),
            'added' => count($missing),
            'already_present' => count($matched) - count($missing),
            'capped' => count($matched) >= self::MAX_LEADS,
        ];
    }
}
