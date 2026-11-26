<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CommunicationLog;
use App\Models\OutreachLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OutreachLeadController extends Controller
{
    public function updateStage(Request $request, OutreachLead $lead)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        $data = $request->validate([
            'stage' => ['required', Rule::in(array_keys(OutreachLead::STAGES))],
        ]);

        $campaign = $lead->campaign;
        $from = $lead->stage;

        if ($campaign->alumni_event_id !== null && $data['stage'] === 'registered') {
            return response()->json([
                'ok' => false,
                'message' => 'Registered comes from the event registration list - add the person there instead.',
            ], 422);
        }

        if ($from !== $data['stage']) {
            $lead->update(['stage' => $data['stage']]);

            AuditLog::record(
                'update',
                'outreach_lead',
                $lead->id,
                sprintf('Moved %s on campaign "%s": %s -> %s',
                    $lead->student->name_english, $campaign->name,
                    OutreachLead::stageLabel($from), OutreachLead::stageLabel($data['stage']))
            );
        }

        return response()->json([
            'ok' => true,
            'counts' => $this->counts($campaign->id),
        ]);
    }

    public function logContact(Request $request, OutreachLead $lead)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($lead->student), 403,
            'You can only record contact for students assigned to you.');

        $student = $lead->student;
        if (!$student->canBeContacted()) {
            return back()->with('error', "{$student->name_english} is marked Do Not Contact, so no contact can be recorded.");
        }

        $data = $request->validate([
            'channel' => ['required', Rule::in(array_keys(CommunicationLog::CHANNELS))],
            'outcome' => ['nullable', Rule::in(array_keys(OutreachLead::STAGES))],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $outcome = $data['outcome'] ?? OutreachLead::STAGE_CONTACTED;
        $when = now();

        $log = CommunicationLog::create([
            'student_id' => $student->id,
            'user_id' => Auth::id(),
            'contact_datetime' => $when,
            'channel' => $data['channel'],
            'direction' => 'outgoing',
            'subject' => 'Outreach: ' . $lead->campaign->name,
            'message_summary' => $data['note'] ?? null,
            'outcome' => OutreachLead::stageLabel($outcome),
        ]);

        $student->update(['last_contacted_date' => $when]);

        $lead->update([
            'stage' => $outcome,
            'channel' => $data['channel'],
            'contacted_at' => $when,
            'note' => $data['note'] ?? null,
        ]);

        AuditLog::record(
            'created',
            'communication_log',
            $log->id,
            "Recorded {$data['channel']} contact with {$student->name_english} on campaign \"{$lead->campaign->name}\""
        );

        return back()->with('success', "Contact recorded for {$student->name_english}.");
    }

    public function destroy(OutreachLead $lead)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        $student = $lead->student;
        $campaign = $lead->campaign;
        $lead->delete();

        AuditLog::record('deleted', 'outreach_lead', $lead->id,
            "Removed {$student->name_english} from campaign \"{$campaign->name}\"");

        return back()->with('success', "{$student->name_english} removed from the board.");
    }

    private function counts(int $campaignId): array
    {
        $rows = OutreachLead::query()
            ->where('outreach_campaign_id', $campaignId)
            ->selectRaw('stage, COUNT(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        $counts = [];
        foreach (array_keys(OutreachLead::STAGES) as $stage) {
            $counts[$stage] = (int) ($rows[$stage] ?? 0);
        }

        return $counts;
    }
}
