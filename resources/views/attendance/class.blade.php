@extends('layouts.app')

@section('title', 'Class Register')

@section('content')
@php($statuses = \App\Models\ClassAttendance::STATUSES)
@php($statusClasses = \App\Models\ClassAttendance::STATUS_CLASSES)
@php($unmarked = max(0, $students->count() - $counts->sum()))

<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-person-check text-primary me-1"></i>Class Attendance Register</h1>
    <div class="page-subtitle">Pick a class and a date, then mark each student present, absent, late or excused. This register is separate from the face and finger check-in log.</div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('attendance.class') }}" class="row g-2 align-items-end" id="classFilterForm">
            <div class="col-sm-6 col-lg-4">
                <label for="study_group_id" class="form-label small mb-1">Class</label>
                <select id="study_group_id" name="study_group_id" class="form-select form-select-sm">
                    <option value="">Select a class…</option>
                    @foreach($studyGroups as $group)
                        <option value="{{ $group->id }}" @selected($studyGroup && $studyGroup->id === $group->id)>
                            {{ $group->name }}{{ $group->code ? ' — ' . $group->code : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label for="date" class="form-label small mb-1">Date</label>
                <input type="date" id="date" name="date" class="form-control form-control-sm" value="{{ $date }}" max="{{ now()->toDateString() }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel me-1"></i>Show roster</button>
                @if($studyGroup && $date !== now()->toDateString())
                    <a href="{{ route('attendance.class', ['study_group_id' => $studyGroup->id]) }}" class="btn btn-sm btn-link">Today</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($studyGroups->isEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        There is no class to register yet. Create one under <a href="{{ route('study-groups.index') }}">Study Groups</a> and add its students first.
    </div>
@elseif(!$studyGroup)
    <div class="alert alert-light border">Select a class above to load its students.</div>
@else
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="badge bg-primary-subtle text-primary">Total {{ $students->count() }}</span>
        @foreach($statuses as $key => $label)
            <span class="badge bg-{{ $statusClasses[$key] }}-subtle text-{{ $statusClasses[$key] }}">{{ $label }} {{ $counts[$key] }}</span>
        @endforeach
        <span class="badge bg-secondary-subtle text-secondary">Not marked {{ $unmarked }}</span>
        @if($studyGroup->study_time)
            <small class="text-muted ms-auto"><i class="bi bi-clock me-1"></i>{{ $studyGroup->study_time }}</small>
        @endif
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h6 mb-0 fw-bold">{{ $studyGroup->name }}</h2>
                <small class="text-muted">{{ \Illuminate\Support\Carbon::parse($date)->format('l, d M Y') }}@if($studyGroup->code) · {{ $studyGroup->code }}@endif</small>
            </div>
            @if($students->isNotEmpty())
                <button type="button" class="btn btn-sm btn-outline-success" id="markAllPresent">
                    <i class="bi bi-check2-all me-1"></i>Mark all present
                </button>
            @endif
        </div>

        @if($students->isEmpty())
            <div class="card-body">
                <div class="text-muted mb-2">This class has no students yet.</div>
                <a href="{{ route('study-groups.show', $studyGroup) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Add students to {{ $studyGroup->name }}
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('attendance.class.store') }}" data-loading id="registerForm">
                @csrf
                <input type="hidden" name="study_group_id" value="{{ $studyGroup->id }}">
                <input type="hidden" name="attendance_date" value="{{ $date }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:3rem">#</th>
                                <th>Student</th>
                                <th class="text-center" style="width:8rem">Scanned in</th>
                                <th style="width:20rem">Status</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php($mark = $marks->get($student->id))
                                @php($scan = $scans->get($student->id))
                                <tr>
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('students.show', $student) }}" class="fw-semibold text-decoration-none">{{ $student->name_english }}</a>
                                        @if($student->name_khmer)<div class="small text-muted">{{ $student->name_khmer }}</div>@endif
                                        <div class="small text-muted">{{ $student->student_code ?: '—' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($scan && $scan->check_in_at)
                                            <span class="badge bg-info-subtle text-info">{{ $scan->check_in_at->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Status for {{ $student->name_english }}">
                                            @foreach($statuses as $key => $label)
                                                <input type="radio" class="btn-check status-radio" name="marks[{{ $student->id }}][status]"
                                                       id="status-{{ $student->id }}-{{ $key }}" value="{{ $key }}" autocomplete="off"
                                                       @checked($mark && $mark->status === $key)>
                                                <label class="btn btn-outline-{{ $statusClasses[$key] }}" for="status-{{ $student->id }}-{{ $key }}">{{ $label }}</label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm mark-note" name="marks[{{ $student->id }}][note]"
                                               maxlength="255" placeholder="Arrived late, sick…"
                                               value="{{ $mark->note ?? '' }}" disabled>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex flex-wrap align-items-center gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save attendance</button>
                    <small class="text-muted">Only the students you tick here are saved. Marking a student again replaces the earlier mark for this date.</small>
                    @if($unmarked < $students->count())
                        <small class="text-success ms-auto"><i class="bi bi-check-circle me-1"></i>{{ $counts->sum() }} of {{ $students->count() }} already marked</small>
                    @endif
                </div>
            </form>
        @endif
    </div>
@endif

@once
    @push('scripts')
    <script>
    (function () {
        var filter = document.getElementById('classFilterForm');
        if (filter) {
            ['study_group_id', 'date'].forEach(function (id) {
                var field = document.getElementById(id);
                if (field) { field.addEventListener('change', function () { filter.submit(); }); }
            });
        }

        // A note without a status would post a half-empty row, so it stays disabled until ticked.
        document.querySelectorAll('.btn-group').forEach(function (group) {
            var row = group.closest('tr');
            var note = row ? row.querySelector('.mark-note') : null;
            if (!note) { return; }
            group.addEventListener('change', function () {
                note.disabled = !group.querySelector('.status-radio:checked');
                if (note.disabled) { note.value = ''; }
            });
            note.disabled = !group.querySelector('.status-radio:checked');
        });

        var allPresent = document.getElementById('markAllPresent');
        if (allPresent) {
            allPresent.addEventListener('click', function () {
                document.querySelectorAll('.btn-group .status-radio[value="present"]').forEach(function (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }
    })();
    </script>
    @endpush
@endonce
@endsection
