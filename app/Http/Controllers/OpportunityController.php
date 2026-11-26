<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OpportunityController extends Controller
{
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'opportunity_type' => ['required', Rule::in(array_keys(Opportunity::TYPES))],
            'description' => ['nullable', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'application_deadline' => ['nullable', 'date'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'application_url' => ['nullable', 'url', 'max:255'],
            'target_course' => ['nullable', 'string', 'max:100'],
            'target_graduation_year' => ['nullable', 'string', 'max:100'],
            'publication_status' => ['required', Rule::in(['draft', 'published', 'closed'])],
        ];
    }

    public function index(Request $request)
    {
        $query = Opportunity::query()->withCount('applications');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('title', 'like', "%$q%")->orWhere('organization', 'like', "%$q%"));
        }
        if ($request->filled('opportunity_type')) {
            $query->where('opportunity_type', $request->input('opportunity_type'));
        }
        if ($request->filled('publication_status')) {
            $query->where('publication_status', $request->input('publication_status'));
        }
        $opportunities = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('opportunities.index', compact('opportunities'));
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        return view('opportunities.form', ['opportunity' => new Opportunity(['publication_status' => 'draft'])]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());
        $data['created_by'] = Auth::id();
        $opportunity = Opportunity::create($data);
        AuditLog::record('created', 'opportunity', $opportunity->id, "Created opportunity '{$opportunity->title}'");

        if ($opportunity->publication_status === 'published') {
            foreach (User::where('is_active', true)->get() as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'new_opportunity',
                    'title' => 'New ' . (Opportunity::TYPES[$opportunity->opportunity_type] ?? 'opportunity'),
                    'message' => $opportunity->title . ' @ ' . $opportunity->organization,
                    'link' => route('opportunities.show', $opportunity),
                ]);
            }
        }

        return redirect()->route('opportunities.show', $opportunity)->with('success', 'Opportunity created.');
    }

    public function show(Opportunity $opportunity)
    {
        $opportunity->load(['applications.student:id,name_english,student_code', 'creator:id,name']);
        $appliedIds = $opportunity->applications->pluck('student_id')->all();
        $availableStudents = Student::where('record_status', 'active')->whereNotIn('id', $appliedIds)
            ->orderBy('name_english')->get(['id', 'name_english', 'student_code']);

        return view('opportunities.show', compact('opportunity', 'availableStudents'));
    }

    public function edit(Opportunity $opportunity)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        return view('opportunities.form', compact('opportunity'));
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());
        $opportunity->update($data);
        AuditLog::record('updated', 'opportunity', $opportunity->id, "Updated opportunity '{$opportunity->title}'");

        return redirect()->route('opportunities.show', $opportunity)->with('success', 'Opportunity updated.');
    }

    public function destroy(Opportunity $opportunity)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $opportunity->delete();
        AuditLog::record('deleted', 'opportunity', $opportunity->id, "Deleted opportunity '{$opportunity->title}'");
        return redirect()->route('opportunities.index')->with('success', 'Opportunity deleted.');
    }

    /** Match eligible alumni by skills, course, interests and graduation year. */
    public function match(Opportunity $opportunity)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        $query = Student::query()->where('record_status', 'active')->where('status', '!=', 'do_not_contact');

        // Interest filter by type
        switch ($opportunity->opportunity_type) {
            case 'job':
                $query->where('interested_jobs', true);
                break;
            case 'internship':
                $query->where('interested_internships', true);
                break;
            case 'volunteer':
                $query->where('interested_volunteering', true);
                break;
            case 'training':
            case 'scholarship':
                $query->where('interested_training', true);
                break;
        }

        // Target course / graduation year
        $targetCourseCode = strtoupper(trim((string) $opportunity->target_course));
        $targetYears = array_filter(array_map('trim', explode(',', (string) $opportunity->target_graduation_year)));
        if ($targetCourseCode || $targetYears) {
            $query->whereHas('studentCourses', function ($w) use ($targetCourseCode, $targetYears) {
                if ($targetCourseCode) {
                    $w->whereHas('course', fn ($w2) => $w2->where('code', $targetCourseCode));
                }
                if ($targetYears) {
                    $w->whereIn('graduation_year', $targetYears);
                }
            });
        }

        // Keyword match from description/requirements against skills
        $keywords = [];
        preg_match_all('/\b[A-Za-z]{4,}\b/', strtolower(($opportunity->requirements ?? '') . ' ' . ($opportunity->description ?? '')), $m);
        $stop = ['with', 'from', 'this', 'that', 'have', 'must', 'should', 'will', 'able', 'work', 'team', 'year', 'years', 'required', 'requirements', 'alumni', 'students', 'open', 'interest', 'related'];
        foreach ($m[0] as $word) {
            if (!in_array($word, $stop)) {
                $keywords[] = $word;
            }
        }
        $keywords = array_slice(array_unique($keywords), 0, 8);

        $appliedIds = $opportunity->applications()->pluck('student_id')->all();
        $matches = $query->whereNotIn('students.id', $appliedIds)->limit(50)->get()->map(function ($s) use ($keywords) {
            $haystack = strtolower(($s->technical_skills ?? '') . ' ' . ($s->professional_skills ?? '') . ' ' . ($s->career_goals ?? ''));
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($haystack, $kw)) {
                    $score += 2;
                }
            }
            if ($s->is_seeking_employment) {
                $score += 1;
            }
            return ['student' => $s, 'score' => $score];
        })->sortByDesc('score')->values();

        return view('opportunities.match', compact('opportunity', 'matches', 'keywords'));
    }

    public function storeApplication(Request $request, Opportunity $opportunity)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'source' => ['required', Rule::in(['matched', 'referred', 'self_applied'])],
            'status' => ['nullable', Rule::in(['interested', 'applied', 'interviewing', 'accepted', 'rejected', 'withdrawn'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $exists = OpportunityApplication::where('opportunity_id', $opportunity->id)->where('student_id', $data['student_id'])->exists();
        if ($exists) {
            return back()->with('error', 'This student already has an application/referral for this opportunity.');
        }

        OpportunityApplication::create([
            'opportunity_id' => $opportunity->id,
            'student_id' => $data['student_id'],
            'source' => $data['source'],
            'status' => $data['status'] ?? 'interested',
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);
        AuditLog::record('referred', 'opportunity_application', $opportunity->id, "Referred student #{$data['student_id']} to '{$opportunity->title}'");

        return back()->with('success', 'Application / referral recorded.');
    }

    public function updateApplication(Request $request, OpportunityApplication $application)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['interested', 'applied', 'interviewing', 'accepted', 'rejected', 'withdrawn'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $application->update($data);

        return back()->with('success', 'Application updated.');
    }

    public function destroyApplication(OpportunityApplication $application)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $application->delete();
        return back()->with('success', 'Application removed.');
    }
}
