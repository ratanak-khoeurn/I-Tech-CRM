@extends('layouts.app')

@section('title', 'Follow-up Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-bell text-primary me-1"></i>Follow-up & Reminder Management</h1>
        <div class="page-subtitle">Tasks, reminders and overdue follow-ups</div>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('follow-ups.index', request()->query()) }}" class="btn btn-primary"><i class="bi bi-list-ul me-1"></i>List</a>
            <a href="{{ route('follow-ups.calendar', ['month' => request('month')]) }}" class="btn btn-outline-primary"><i class="bi bi-calendar3 me-1"></i>Calendar</a>
        </div>
        @if(auth()->user()->canManageStudents())
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal"><i class="bi bi-plus-lg me-1"></i>New Task</button>
        @endif
    </div>
</div>

<!-- Quick filters -->
<ul class="nav nav-pills mb-3 small">
    @foreach(['open' => 'Open Tasks', 'overdue' => 'Overdue', 'today' => 'Due Today', 'week' => 'This Week', 'completed' => 'Completed', 'all' => 'All'] as $k => $lbl)
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ ($filter ?? 'open') === $k ? 'active' : '' }}"
               href="{{ route('follow-ups.index', array_merge(request()->query(), ['filter' => $k])) }}">{{ $lbl }}</a>
        </li>
    @endforeach
</ul>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="col-md-3">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Task or student..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Student</label>
                <select name="student_id" class="form-select form-select-sm">
                    <option value="">All students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" @selected(request('student_id') == $s->id)>{{ $s->name_english }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-md-2">
                <label class="form-label mb-0">Staff</label>
                <select name="assigned_to" class="form-select form-select-sm">
                    <option value="">Anyone</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" @selected(request('assigned_to') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label mb-0">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">Any</option>
                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('priority') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any</option>
                    @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('follow-ups.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Task</th><th>Student</th><th>Priority</th><th>Due Date</th><th>Assigned</th><th>Status</th><th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td style="max-width:260px">
                            <div class="fw-semibold small">{{ $task->title }}</div>
                            <div class="text-muted text-truncate" style="font-size:.75rem">{{ $task->description }}</div>
                            @if($task->result)<div class="text-success" style="font-size:.73rem"><i class="bi bi-check2 me-1"></i>{{ \Illuminate\Support\Str::limit($task->result, 60) }}</div>@endif
                        </td>
                        <td><a href="{{ route('students.show', [$task->student_id, 'tab' => 'tasks']) }}" class="small text-decoration-none">{{ $task->student?->name_english }}</a></td>
                        <td><span class="badge bg-{{ ['high' => 'danger', 'medium' => 'warning', 'low' => 'secondary'][$task->priority] }}">{{ ucfirst($task->priority) }}</span></td>
                        <td class="small {{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">
                            {{ $task->due_date->format('M d, Y') }}
                            @if($task->isOverdue())<div style="font-size:.68rem">OVERDUE ({{ (int) $task->due_date->diffInDays(now()) }}d)</div>@endif
                            @if($task->reminder_enabled && $task->reminder_at)<div class="text-muted" style="font-size:.68rem"><i class="bi bi-alarm"></i> {{ $task->reminder_at->format('M d H:i') }}</div>@endif
                        </td>
                        <td class="small">{{ $task->assignee?->name ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('follow-ups.status', $task) }}">
                                @csrf @method('PATCH')
                                <select name="status" class="form-select form-select-sm py-0" style="font-size:.75rem" onchange="this.form.submit()">
                                    @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $lbl)
                                        <option value="{{ $k }}" @selected($task->status === $k)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="text-end text-nowrap">
                            @if(auth()->user()->canManageStudents())
                                @if(in_array($task->status, ['pending', 'in_progress']))
                                    <button class="btn btn-sm btn-outline-success" title="Complete with result"
                                            data-bs-toggle="modal" data-bs-target="#completeModal"
                                            data-id="{{ $task->id }}" data-title="{{ $task->title }}">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
                                    <a href="{{ route('follow-ups.edit', $task) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                @endif
                                <form action="{{ route('follow-ups.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('You are about to delete this **follow-up task**. This action cannot be undone. Would you like to proceed?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="bi bi-check2-circle"></i>No tasks in this view</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap">
        <small class="text-muted">{{ $tasks->total() }} task(s)</small>
        {{ $tasks->links() }}
    </div>
</div>

@if(auth()->user()->canManageStudents())
<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('follow-ups.store') }}">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-1"></i>New Follow-up Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Student <span class="required-mark">*</span></label>
                    <select name="student_id" class="form-select" required>
                        <option value="">— Select student —</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name_english }} ({{ $s->student_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Task Title <span class="required-mark">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="255">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Due Date <span class="required-mark">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ now()->addWeek()->toDateString() }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="{{ auth()->id() }}">Me</option>
                            @foreach($staff as $s)
                                @if($s->id !== auth()->id())<option value="{{ $s->id }}">{{ $s->name }}</option>@endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Reminder At</label>
                        <input type="datetime-local" name="reminder_at" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Create Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Task Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" id="completeForm" action="">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="completed">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check2-circle me-1"></i>Complete Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small">Recording the result of: <strong id="completeTaskTitle"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Result of this follow-up <span class="required-mark">*</span></label>
                    <textarea name="result" class="form-control" rows="3" required placeholder="What was the outcome of this follow-up?"></textarea>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="scheduleAnother" value="1">
                    <label class="form-check-label small" for="scheduleAnother">Schedule another follow-up after completing</label>
                </div>
                <div id="rescheduleFields" class="mt-2 d-none">
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label small">Next task title</label>
                            <input type="text" class="form-control form-control-sm" id="nextTitle" placeholder="Follow-up call...">
                        </div>
                        <div class="col-5">
                            <label class="form-label small">Due date</label>
                            <input type="date" class="form-control form-control-sm" id="nextDue" value="{{ now()->addWeeks(2)->toDateString() }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i>Mark Completed</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const completeModal = document.getElementById('completeModal');
    if (!completeModal) return;

    let completeAction = '';
    let completeStudentId = '';

    completeModal.addEventListener('show.bs.modal', e => {
        const btn = e.relatedTarget;
        document.getElementById('completeTaskTitle').textContent = btn.dataset.title;
        completeAction = '/follow-ups/' + btn.dataset.id + '/status';
        const row = btn.closest('tr');
        const link = row && row.querySelector('a[href*="/students/"]');
        completeStudentId = link ? link.getAttribute('href').match(/\/students\/(\d+)/)[1] : '';
    });

    document.getElementById('scheduleAnother').addEventListener('change', function () {
        document.getElementById('rescheduleFields').classList.toggle('d-none', !this.checked);
    });

    document.getElementById('completeForm').addEventListener('submit', function (e) {
        const wantReschedule = document.getElementById('scheduleAnother').checked && document.getElementById('nextTitle').value.trim();
        if (!wantReschedule) return; // plain completion: let the form submit normally

        e.preventDefault();
        const form = this;
        form.querySelector('button[type=submit]').disabled = true;
        form.action = completeAction;

        // 1) Complete the current task via fetch
        fetch(completeAction, {
            method: 'POST',
            headers: { 'Accept': 'text/html' },
            body: new URLSearchParams(new FormData(form)).toString(),
            redirect: 'follow',
        }).then(() => {
            // 2) Chain-create the next follow-up task with a normal form POST
            if (!completeStudentId) { window.location = '{{ route('follow-ups.index') }}'; return; }
            const resched = document.createElement('form');
            resched.method = 'POST';
            resched.action = '{{ route('follow-ups.store') }}';
            const token = document.querySelector('meta[name="csrf-token"]').content;
            resched.innerHTML =
                '<input type="hidden" name="_token" value="' + token + '">' +
                '<input type="hidden" name="title" value="' + document.getElementById('nextTitle').value.replace(/"/g, '&quot;') + '">' +
                '<input type="hidden" name="due_date" value="' + document.getElementById('nextDue').value + '">' +
                '<input type="hidden" name="priority" value="medium">' +
                '<input type="hidden" name="student_id" value="' + completeStudentId + '">';
            document.body.appendChild(resched);
            resched.submit();
        }).catch(() => window.location.reload());
    });

    @if($completeTask)
        // Deep-link: open the completion modal for ?complete={id}
        const btn = document.querySelector('[data-id="{{ $completeTask->id }}"][data-bs-target="#completeModal"]');
        if (btn) btn.click();
    @endif
});
</script>
@endpush
