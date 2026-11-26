<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CommunicationLog;
use App\Models\FollowUpTask;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'contact_datetime' => ['required', 'date'],
            'channel' => ['required', Rule::in(array_keys(CommunicationLog::CHANNELS))],
            'direction' => ['required', Rule::in(['incoming', 'outgoing'])],
            'subject' => ['required', 'string', 'max:255'],
            'message_summary' => ['nullable', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:255'],
            'followup_required' => ['nullable', 'boolean'],
            'next_followup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function index(Request $request)
    {
        $query = CommunicationLog::query()->with(['student:id,name_english,student_code,photo', 'user:id,name']);

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('subject', 'like', "%$q%")
                    ->orWhere('message_summary', 'like', "%$q%")
                    ->orWhereHas('student', fn ($w2) => $w2->where('name_english', 'like', "%$q%")->orWhere('student_code', 'like', "%$q%"));
            });
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->input('direction'));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('contact_datetime', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('contact_datetime', '<=', $request->input('date_to'));
        }

        $logs = $query->latest('contact_datetime')->paginate(15)->withQueryString();
        $students = Student::where('record_status', 'active')->orderBy('name_english')->get(['id', 'name_english', 'student_code']);

        return view('communications.index', compact('logs', 'students'));
    }

    public function store(Request $request, ?Student $student = null)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        if ($student) {
            $request->merge(['student_id' => $student->id]);
        }
        $data = $request->validate($this->rules());

        $target = Student::findOrFail($data['student_id']);
        abort_unless(Auth::user()->canAccessStudent($target), 403, 'You can only manage communication for students assigned to you.');
        if (!$target->canBeContacted() && $data['direction'] === 'outgoing') {
            return back()->with('error', 'This student is marked "Do Not Contact". Outgoing communications are blocked.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('communications', 'public');
        }

        DB::transaction(function () use ($data, $attachmentPath, $target) {
            $log = CommunicationLog::create([
                'student_id' => $target->id,
                'user_id' => Auth::id(),
                'contact_datetime' => $data['contact_datetime'],
                'channel' => $data['channel'],
                'direction' => $data['direction'],
                'subject' => $data['subject'],
                'message_summary' => $data['message_summary'] ?? null,
                'outcome' => $data['outcome'] ?? null,
                'followup_required' => !empty($data['followup_required']),
                'next_followup_date' => $data['next_followup_date'] ?? null,
                'attachment' => $attachmentPath,
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            $target->update(['last_contacted_date' => $data['contact_datetime']]);

            // Auto-create a follow-up task when required
            if (!empty($data['followup_required']) && !empty($data['next_followup_date'])) {
                FollowUpTask::create([
                    'student_id' => $target->id,
                    'assigned_to' => $target->assigned_staff_id ?? Auth::id(),
                    'created_by' => Auth::id(),
                    'communication_log_id' => $log->id,
                    'title' => 'Follow-up: ' . $data['subject'],
                    'description' => 'Auto-created from communication on ' . $log->contact_datetime->format('M d, Y') . '.',
                    'priority' => 'medium',
                    'due_date' => $data['next_followup_date'],
                    'status' => 'pending',
                ]);
                $target->update(['next_followup_date' => $data['next_followup_date']]);
            }

            AuditLog::record('created', 'communication_log', $log->id, "Logged {$data['channel']} interaction with {$target->name_english}");
        });

        $redirect = $student
            ? redirect()->route('students.show', [$student, 'tab' => 'communication'])
            : redirect()->route('communications.index');

        return $redirect->with('success', 'Interaction recorded successfully.');
    }

    public function edit(CommunicationLog $communication)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($communication->student), 403, 'You can only manage communication for students assigned to you.');
        $students = Student::where('record_status', 'active')->orderBy('name_english')->get(['id', 'name_english', 'student_code']);
        return view('communications.edit', ['log' => $communication, 'students' => $students]);
    }

    public function update(Request $request, CommunicationLog $communication)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($communication->student), 403, 'You can only manage communication for students assigned to you.');
        $data = $request->validate($this->rules());
        $data['followup_required'] = !empty($data['followup_required']);
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('communications', 'public');
        } else {
            unset($data['attachment']);
        }
        $communication->update($data);
        AuditLog::record('updated', 'communication_log', $communication->id, 'Updated communication record');

        return redirect()->route('communications.index')->with('success', 'Communication record updated.');
    }

    public function destroy(CommunicationLog $communication)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($communication->student), 403, 'You can only manage communication for students assigned to you.');
        $communication->delete();
        AuditLog::record('deleted', 'communication_log', $communication->id, 'Deleted communication record');
        return back()->with('success', 'Communication record deleted.');
    }
}
