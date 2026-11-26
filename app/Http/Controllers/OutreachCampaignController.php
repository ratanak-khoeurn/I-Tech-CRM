<?php

namespace App\Http\Controllers;

use App\Models\AlumniEvent;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\CommunicationLog;
use App\Models\Course;
use App\Models\OutreachCampaign;
use App\Models\OutreachLead;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\StudyGroup;
use App\Services\AudienceBuilder;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OutreachCampaignController extends Controller
{
    private const COLUMN_LIMIT = 200;

    /** Rows the "add people" panel can show at once. */
    private const ADD_PANEL_LIMIT = 200;

    public function __construct(private AudienceBuilder $audience)
    {
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', Rule::in(array_keys(OutreachCampaign::PURPOSES))],
            'alumni_event_id' => ['nullable', 'integer', Rule::exists('alumni_events', 'id')->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'completed', 'archived'])],
            'audience' => ['nullable', 'array'],
            'audience.course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'audience.campus_id' => array_merge(['nullable', 'integer'], CampusContext::campusRules(false)),
            'audience.graduation_year' => ['nullable', 'integer', 'min:1980', 'max:2100'],
            'audience.student_stage' => ['nullable', Rule::in(array_keys(Student::STAGE_LABELS))],
            'audience.graduation_status' => ['nullable', Rule::in(array_keys(Student::GRADUATION_STATUS_LABELS))],
            'audience.engagement_status' => ['nullable', Rule::in(['engaged', 'semi_engaged', 'not_engaged'])],
            'audience.alumni_group' => ['nullable', 'string', 'max:100'],
            'audience.flags' => ['nullable', 'array'],
            'audience.flags.*' => ['boolean'],
        ];
    }

    /** Keep only the filter keys we understand, so a stray field can't break the builder. */
    private function cleanAudience(Request $request): ?array
    {
        $audience = $request->input('audience');
        if (!is_array($audience)) {
            return null;
        }

        $clean = [];
        foreach (['course_id', 'campus_id', 'graduation_year', 'student_stage', 'graduation_status', 'engagement_status', 'alumni_group'] as $key) {
            if (!empty($audience[$key])) {
                $clean[$key] = is_numeric($audience[$key]) ? (int) $audience[$key] : trim((string) $audience[$key]);
            }
        }

        $flags = [];
        foreach (array_keys(OutreachCampaign::AUDIENCE_FLAGS) as $flag) {
            if (!empty($audience['flags'][$flag])) {
                $flags[$flag] = true;
            }
        }
        if ($flags !== []) {
            $clean['flags'] = $flags;
        }

        return $clean === [] ? null : $clean;
    }

    public function index(Request $request)
    {
        $purposes = array_keys(OutreachCampaign::PURPOSES);
        $purpose = $purposes !== [] && in_array($request->input('purpose'), $purposes, true)
            ? $request->input('purpose') : null;
        $statuses = ['active', 'completed', 'archived'];
        $status = in_array($request->input('status'), $statuses, true) ? $request->input('status') : null;
        $q = trim((string) $request->input('q'));

        // "needs attention" is a display filter on the stage counts, not on the rows.
        $attention = $request->input('attention') === '1';

        $query = OutreachCampaign::query()
            ->with('event:id,title,start_datetime', 'creator:id,name')
            ->withCount('leads');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                    ->orWhereHas('event', fn ($e) => $e->where('title', 'like', "%$q%"));
            });
        }
        if ($purpose !== null) {
            $query->where('purpose', $purpose);
        }
        if ($status !== null) {
            $query->where('status', $status);
        }
        if ($request->input('event') === '1') {
            $query->whereNotNull('alumni_event_id');
        }

        // Campus is opt-in here: a campaign is not owned by one campus, so unlike the
        // student lists this page starts unfiltered and only narrows when asked.
        $campusParam = $request->input('campus_id');
        $campusId = is_numeric($campusParam) ? (int) $campusParam : null;
        if ($campusId !== null) {
            $query->whereHas('leads', fn ($w) => $w->whereHas('student.studentCourses',
                fn ($s) => $s->where('campus_id', $campusId)));
        }

        $campaigns = $query->latest('id')->get();

        foreach ($campaigns as $campaign) {
            $campaign->stage_counts = $campaign->leads()
                ->selectRaw('stage, COUNT(*) as total')
                ->groupBy('stage')
                ->pluck('total', 'stage');
        }

        if ($attention) {
            $campaigns = $campaigns->filter(
                fn ($c) => (int) ($c->stage_counts[OutreachLead::STAGE_TO_CONTACT] ?? 0) > 0
                    && $c->status === 'active'
            );
        }

        return view('outreach.index', [
            'campaigns' => $campaigns,
            'stages' => OutreachLead::STAGES,
            'colors' => OutreachLead::STAGE_COLORS,
            'canManage' => Auth::user()->canManageStudents(),
            'purposes' => OutreachCampaign::PURPOSES,
            'filters' => ['q' => $q, 'purpose' => $purpose, 'status' => $status, 'campus' => $campusId,
                'event' => $request->input('event') === '1', 'attention' => $attention],
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'unfilteredTotal' => OutreachCampaign::count(),
            'purposeTotals' => $this->totalsBy('purpose'),
            'statusTotals' => $this->totalsBy('status'),
            'toContactTotal' => OutreachLead::where('stage', OutreachLead::STAGE_TO_CONTACT)
                ->whereHas('campaign', fn ($w) => $w->where('status', 'active'))
                ->count(),
            'handledTotal' => OutreachLead::whereIn('stage', ['interested', 'registered', 'declined'])->count(),
            'leadTotal' => OutreachLead::count(),
        ]);
    }

    /** Campaign counts per purpose/status value, for the filter chips. */
    private function totalsBy(string $column): array
    {
        return OutreachCampaign::query()
            ->selectRaw($column . ', COUNT(*) as total')
            ->groupBy($column)
            ->pluck('total', $column)
            ->all();
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        return view('outreach.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());

        $campaign = OutreachCampaign::create([
            'name' => $data['name'],
            'purpose' => $data['purpose'],
            'alumni_event_id' => $data['alumni_event_id'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'audience_filters' => $this->cleanAudience($request),
            'created_by' => Auth::id(),
        ]);

        AuditLog::record('created', 'outreach_campaign', $campaign->id, "Created outreach campaign '{$campaign->name}'");

        return redirect()->route('outreach.board', $campaign)->with('success', 'Campaign created. Build the audience to add people.');
    }

    public function edit(OutreachCampaign $campaign)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        return view('outreach.form', $this->formData($campaign));
    }

    public function update(Request $request, OutreachCampaign $campaign)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());

        $campaign->update([
            'name' => $data['name'],
            'purpose' => $data['purpose'],
            'alumni_event_id' => $data['alumni_event_id'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'audience_filters' => $this->cleanAudience($request),
        ]);

        AuditLog::record('updated', 'outreach_campaign', $campaign->id, "Updated outreach campaign '{$campaign->name}'");

        return redirect()->route('outreach.board', $campaign)->with('success', 'Campaign updated.');
    }

    public function destroy(OutreachCampaign $campaign)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $campaign->delete();
        AuditLog::record('deleted', 'outreach_campaign', $campaign->id, "Archived outreach campaign '{$campaign->name}'");

        return redirect()->route('outreach.index')->with('success', 'Campaign removed.');
    }

    public function board(Request $request, OutreachCampaign $campaign)
    {
        $synced = OutreachLead::syncRegisteredFromEvent($campaign);

        $base = OutreachLead::query()
            ->where('outreach_campaign_id', $campaign->id)
            ->with([
                'student:id,name_english,name_khmer,student_code,phone,telegram,email,photo,status,preferred_channel,last_contacted_date,assigned_staff_id',
                'student.assignedStaff:id,name',
            ]);

        // Staff land on their active campus unless they chose otherwise.
        $campusId = CampusContext::filterFor($request);
        if ($campusId !== null) {
            $base->whereHas('student.studentCourses', fn ($w) => $w->where('campus_id', $campusId));
        }

        if ($q = trim((string) $request->input('q'))) {
            $base->whereHas('student', function ($w) use ($q) {
                $w->where('name_english', 'like', "%$q%")
                    ->orWhere('name_khmer', 'like', "%$q%")
                    ->orWhere('student_code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%");
            });
        }

        $grouped = [];
        $counts = [];
        foreach (array_keys(OutreachLead::STAGES) as $stage) {
            $stageQuery = (clone $base)->where('stage', $stage)->orderBy('id');
            $counts[$stage] = (clone $stageQuery)->count();
            $grouped[$stage] = $stageQuery->limit(self::COLUMN_LIMIT)->get();
        }

        return view('outreach.board', [
            'campaign' => $campaign->load('event:id,title,start_datetime,status,event_type', 'creator:id,name'),
            'columns' => OutreachLead::STAGES,
            'colors' => OutreachLead::STAGE_COLORS,
            'grouped' => $grouped,
            'counts' => $counts,
            'columnLimit' => self::COLUMN_LIMIT,
            'totalLeads' => array_sum($counts),
            'syncedRegistered' => $synced,
            'canManage' => Auth::user()->canManageStudents(),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'campusFilter' => $campusId,
            'candidates' => $this->candidates($request, $campaign),
            'classes' => StudyGroup::with('campus:id,name')->orderBy('code')->get(['id', 'name', 'code', 'campus_id', 'study_time', 'graduation_year']),
            'classFilter' => is_numeric($request->input('class_id')) ? (int) $request->input('class_id') : null,
            'audienceCount' => $this->matchCount($campaign),
            'channels' => CommunicationLog::CHANNELS,
            'flagLabels' => OutreachCampaign::AUDIENCE_FLAGS,
        ]);
    }

    public function buildAudience(OutreachCampaign $campaign)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        if ($campaign->audience_filters === null || $campaign->audience_filters === []) {
            return back()->with('error', 'Save at least one audience filter first (course, campus, graduation year or an interest flag).');
        }

        $result = $this->audience->apply($campaign, Auth::id());

        AuditLog::record(
            'updated',
            'outreach_campaign',
            $campaign->id,
            "Built audience for '{$campaign->name}': {$result['added']} added, {$result['already_present']} already present"
        );

        $message = "Audience built: {$result['added']} new card(s), {$result['already_present']} already on the board.";
        if ($result['capped']) {
            $message .= ' Only the first ' . AudienceBuilder::MAX_LEADS . ' matching students were taken — narrow the filters.';
        }

        return redirect()->route('outreach.board', $campaign)->with('success', $message);
    }

    public function addLead(Request $request, OutreachCampaign $campaign)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        if (!$student->canBeContacted()) {
            return back()->with('error', "{$student->name_english} is marked Do Not Contact and cannot be added to an outreach campaign.");
        }

        $existing = OutreachLead::where('outreach_campaign_id', $campaign->id)->where('student_id', $student->id)->first();
        if ($existing) {
            return back()->with('warning', "{$student->name_english} is already on this board.");
        }

        $lead = OutreachLead::create([
            'outreach_campaign_id' => $campaign->id,
            'student_id' => $student->id,
            'stage' => OutreachLead::STAGE_TO_CONTACT,
            'created_by' => Auth::id(),
        ]);

        AuditLog::record('created', 'outreach_lead', $lead->id, "Added {$student->name_english} to campaign '{$campaign->name}'");

        return back()->with('success', "{$student->name_english} added to the board.");
    }

    /**
     * People the staff can still add to this board, picked by class (study group)
     * and/or a search term. Everyone already on the board is excluded so a pick can
     * never create a duplicate card.
     */
    private function candidates(Request $request, OutreachCampaign $campaign): ?array
    {
        $term = trim((string) $request->input('add_q'));
        $classId = $request->input('class_id');
        $classId = is_numeric($classId) && (int) $classId > 0 ? (int) $classId : null;

        if ($term === '' && $classId === null) {
            return null;
        }

        $existing = OutreachLead::where('outreach_campaign_id', $campaign->id)->pluck('student_id')->all();

        $query = Student::query()
            ->where('record_status', 'active')
            ->whereNotIn('id', $existing)
            ->when($classId, fn ($q) => $q->whereHas('studentCourses',
                fn ($w) => $w->where('study_group_id', $classId)))
            ->when($term !== '', fn ($q) => $q->where(function ($w) use ($term) {
                $w->where('name_english', 'like', "%$term%")
                    ->orWhere('name_khmer', 'like', "%$term%")
                    ->orWhere('student_code', 'like', "%$term%")
                    ->orWhere('phone', 'like', "%$term%");
            }))
            ->orderBy('name_english');

        $total = (clone $query)->count();
        $students = $query->limit(self::ADD_PANEL_LIMIT)->get([
            'id', 'name_english', 'name_khmer', 'student_code', 'phone', 'email', 'telegram',
            'student_stage', 'status', 'preferred_channel', 'last_contacted_date',
        ]);

        return [
            'students' => $students,
            'total' => $total,
            'capped' => $total > $students->count(),
            'class' => $classId === null ? null : StudyGroup::with('campus:id,name')->find($classId),
            'term' => $term,
        ];
    }

    /** Checkbox bulk-add — the single-card endpoint stays for the search row. */
    public function addLeads(Request $request, OutreachCampaign $campaign)
    {
        abort_unless($request->user()->canManageStudents(), 403);

        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $existing = OutreachLead::where('outreach_campaign_id', $campaign->id)->pluck('student_id')->all();
        $blocked = [];
        $duplicate = [];
        $added = 0;

        foreach (Student::whereIn('id', $data['student_ids'])->get() as $student) {
            if (in_array((int) $student->id, $existing, true)) {
                $duplicate[] = $student->name_english;
                continue;
            }
            if (!$student->canBeContacted()) {
                $blocked[] = $student->name_english;
                continue;
            }

            OutreachLead::create([
                'outreach_campaign_id' => $campaign->id,
                'student_id' => $student->id,
                'stage' => OutreachLead::STAGE_TO_CONTACT,
                'created_by' => $request->user()->id,
            ]);
            $existing[] = (int) $student->id;
            $added++;
        }

        if ($added > 0) {
            AuditLog::record(
                'created',
                'outreach_lead',
                $campaign->id,
                "Added {$added} student(s) to outreach campaign '{$campaign->name}'"
            );
        }

        $message = $added > 0 ? "{$added} student(s) added to the board." : 'No card was added.';
        if ($blocked !== []) {
            $message .= ' ' . count($blocked) . ' skipped (Do Not Contact).';
        }
        if ($duplicate !== []) {
            $message .= ' ' . count($duplicate) . ' already on the board.';
        }

        return redirect()->back()->with($added > 0 ? 'success' : 'warning', $message);
    }

    private function matchCount(?OutreachCampaign $campaign): ?int
    {
        $filters = $campaign?->audience_filters;

        return $filters === null || $filters === [] ? null : $this->audience->countFor($filters);
    }

    private function formData(?OutreachCampaign $campaign): array
    {
        return [
            'campaign' => $campaign ?? new OutreachCampaign(['status' => 'active', 'purpose' => 'event']),
            'audienceCount' => $this->matchCount($campaign),
            'events' => AlumniEvent::where('status', '!=', 'cancelled')->orderByDesc('start_datetime')->get(['id', 'title', 'start_datetime', 'status']),
            'courses' => Course::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'gradYears' => StudentCourse::whereNotNull('graduation_year')->distinct()->orderByDesc('graduation_year')->pluck('graduation_year'),
            'purposes' => OutreachCampaign::PURPOSES,
            'stages' => OutreachLead::STAGES,
            'flagLabels' => OutreachCampaign::AUDIENCE_FLAGS,
            'studentStages' => Student::STAGE_LABELS,
            'graduationStatuses' => Student::GRADUATION_STATUS_LABELS,
        ];
    }
}
