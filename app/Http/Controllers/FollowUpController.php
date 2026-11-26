<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FollowUpTask;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['required', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'reminder_enabled' => ['nullable', 'boolean'],
            'reminder_at' => ['nullable', 'date'],
        ];
    }

    public function index(Request $request)
    {
        $query = FollowUpTask::query()->with(['student:id,name_english,student_code,photo', 'assignee:id,name']);

        if (!$request->user()->isAdmin()) {
            $query->where('assigned_to', $request->user()->id);
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%$q%")
                    ->orWhereHas('student', fn ($w2) => $w2->where('name_english', 'like', "%$q%"));
            });
        }
        $filter = $request->input('filter', 'open');
        switch ($filter) {
            case 'overdue':
                $query->whereIn('status', ['pending', 'in_progress'])->whereDate('due_date', '<', now());
                break;
            case 'today':
                $query->whereIn('status', ['pending', 'in_progress'])->whereDate('due_date', now());
                break;
            case 'week':
                $query->whereIn('status', ['pending', 'in_progress'])->whereBetween('due_date', [now()->startOfDay(), now()->endOfWeek()]);
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'all':
                break;
            default: // open
                $query->whereIn('status', ['pending', 'in_progress']);
        }
        foreach (['student_id', 'assigned_to', 'priority', 'status'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        $tasks = $query->orderByRaw("FIELD(status,'pending','in_progress','completed','cancelled')")
            ->orderBy('due_date')
            ->paginate(15)->withQueryString();

        $completeTask = $request->integer('complete') ? FollowUpTask::with('student')->find($request->integer('complete')) : null;
        $students = Student::where('record_status', 'active')->orderBy('name_english')->get(['id', 'name_english', 'student_code']);
        $staff = User::whereIn('role_id', \App\Models\Role::whereIn('name', ['super_admin', 'admin', 'staff'])->pluck('id'))->where('is_active', true)->orderBy('name')->get();

        return view('followups.index', compact('tasks', 'students', 'staff', 'filter', 'completeTask'));
    }

    public function calendar(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = !empty($validated['month'])
            ? Carbon::createFromFormat('Y-m-d', $validated['month'] . '-01')
            : Carbon::now();
        $start = $month->copy()->startOfMonth()->startOfWeek();
        $end = $month->copy()->endOfMonth()->endOfWeek();

        $query = FollowUpTask::query()->with('student:id,name_english')
            ->whereBetween('due_date', [$start, $end])
            ->whereIn('status', ['pending', 'in_progress']);
        if (!$request->user()->isAdmin()) {
            $query->where('assigned_to', $request->user()->id);
        }
        $tasksByDate = $query->get()->groupBy(fn ($t) => $t->due_date->format('Y-m-d'));

        return view('followups.calendar', ['month' => $month, 'start' => $start, 'end' => $end, 'tasksByDate' => $tasksByDate]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());
        abort_unless(Auth::user()->canAccessStudent(Student::find($data['student_id'])), 403, 'You can only manage follow-ups for students assigned to you.');
        $data['created_by'] = Auth::id();
        $data['reminder_enabled'] = !empty($data['reminder_at']);
        $data['assigned_to'] = $data['assigned_to'] ?? Auth::id();

        $task = FollowUpTask::create($data);

        // notify assignee
        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'followup_reminder',
                'title' => 'New follow-up task assigned',
                'message' => $task->title . ' — due ' . $task->due_date->format('M d, Y'),
                'link' => route('students.show', [$task->student_id, 'tab' => 'tasks']),
            ]);
        }

        AuditLog::record('created', 'follow_up_task', $task->id, "Created follow-up task '{$task->title}'");

        if ($request->input('redirect_to') === 'student') {
            return redirect()->route('students.show', [$task->student_id, 'tab' => 'tasks'])->with('success', 'Follow-up task scheduled.');
        }
        return redirect()->route('follow-ups.index')->with('success', 'Follow-up task created.');
    }

    public function edit(FollowUpTask $task)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($task->student), 403, 'You can only manage follow-ups for students assigned to you.');
        $students = Student::where('record_status', 'active')->orderBy('name_english')->get(['id', 'name_english', 'student_code']);
        $staff = User::whereIn('role_id', \App\Models\Role::whereIn('name', ['super_admin', 'admin', 'staff'])->pluck('id'))->where('is_active', true)->orderBy('name')->get();
        return view('followups.edit', compact('task', 'students', 'staff'));
    }

    public function update(Request $request, FollowUpTask $task)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($task->student), 403, 'You can only manage follow-ups for students assigned to you.');
        $data = $request->validate($this->rules());
        $data['reminder_enabled'] = !empty($data['reminder_at']);
        $task->update($data);
        AuditLog::record('updated', 'follow_up_task', $task->id, "Updated task '{$task->title}'");

        return redirect()->route('follow-ups.index')->with('success', 'Task updated.');
    }

    public function updateStatus(Request $request, FollowUpTask $task)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($task->student), 403, 'You can only manage follow-ups for students assigned to you.');
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'result' => ['nullable', 'string', 'max:5000'],
        ]);
        $task->status = $data['status'];
        if (isset($data['result'])) {
            $task->result = $data['result'];
        }
        if ($data['status'] === 'completed') {
            $task->completed_at = now();
        }
        $task->save();
        AuditLog::record('status_changed', 'follow_up_task', $task->id, "Task '{$task->title}' → {$data['status']}");

        return back()->with('success', 'Task status updated to ' . str_replace('_', ' ', $data['status']) . '.');
    }

    public function destroy(FollowUpTask $task)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($task->student), 403, 'You can only manage follow-ups for students assigned to you.');
        $task->delete();
        AuditLog::record('deleted', 'follow_up_task', $task->id, "Deleted task '{$task->title}'");
        return back()->with('success', 'Task deleted.');
    }
}
