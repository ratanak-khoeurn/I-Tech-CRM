@extends('layouts.app')

@section('title', 'Student Pipeline')

@push('styles')
<link href="{{ asset('css/kanban-board.css') }}" rel="stylesheet">
<style>
    @foreach ($colors as $stage => $color)
        .kb-col[data-stage="{{ $stage }}"] .kb-dot,
        .kb-col[data-stage="{{ $stage }}"] .kb-count { background: var(--bs-{{ $color }}); }
        .kb-card[data-stage="{{ $stage }}"] { border-left-color: var(--bs-{{ $color }}); }
    @endforeach
</style>
@endpush

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-kanban text-primary me-1"></i>Student Pipeline</h1>
    <div class="page-subtitle">
        {{ $totalStudents }} active student(s) across stages
        @if ($canManage) · drag a card to another column to change its stage @else · read-only for your role @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('students.pipeline') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">Search</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Name, code, phone or email" value="{{ $filters['q'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">Course</label>
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" @selected((int) ($filters['course_id'] ?? 0) === (int) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">Campus</label>
                <select name="campus_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($campuses as $c)
                        <option value="{{ $c->id }}" @selected(\App\Support\CampusContext::filterFor(request()) === (int) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">Grad Year</label>
                <select name="graduation_year" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($gradYears as $y)
                        <option value="{{ $y }}" @selected((string) ($filters['graduation_year'] ?? '') === (string) $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ route('students.pipeline') }}" class="btn btn-light btn-sm border"><i class="bi bi-arrow-counterclockwise"></i></a>
                <a href="{{ route('students.index') }}" class="btn btn-light btn-sm border">List view</a>
            </div>
        </form>
    </div>
</div>

<div class="kb-wrap {{ $canManage ? 'can-manage' : '' }}">
    <div class="kb-board" id="pipelineBoard" data-can-manage="{{ $canManage ? 1 : 0 }}"
        data-field="pipeline_stage" data-empty-text="No students in this stage.">
        @foreach ($columns as $stage => $label)
            <div class="kb-col" data-stage="{{ $stage }}">
                <div class="kb-col-head">
                    <span class="kb-dot"></span>
                    <span>{{ $label }}</span>
                    <span class="kb-count" data-count-for="{{ $stage }}">{{ $counts[$stage] }}</span>
                </div>
                <div class="kb-body" data-stage="{{ $stage }}">
                    @forelse ($grouped[$stage] as $s)
                        <div class="kb-card" data-card-id="{{ $s->id }}" data-stage="{{ $stage }}"
                            @if ($canManage) draggable="true" @endif
                            data-url="{{ route('students.pipeline-stage', $s) }}"
                            data-name="{{ $s->name_english }}">
                            <a class="kb-name" href="{{ route('students.show', $s) }}">{{ $s->name_english }}</a>
                            <div class="kb-meta">{{ $s->student_code }}@if ($s->assignedStaff) · {{ $s->assignedStaff->name }} @endif</div>
                            @if ($s->studentCourses->isNotEmpty())
                                <div class="kb-meta text-truncate" title="{{ $s->studentCourses->pluck('course.name')->filter()->join(', ') }}">
                                    <i class="bi bi-book me-1"></i>{{ $s->studentCourses->pluck('course.name')->filter()->join(', ') ?: '—' }}
                                </div>
                            @endif
                            <div class="kb-meta">
                                <i class="bi bi-telephone me-1"></i>{{ $s->phone }}
                                @if ($s->last_contacted_date)
                                    <span class="ms-2"><i class="bi bi-chat-dots me-1"></i>{{ $s->last_contacted_date->format('Y-m-d') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="kb-empty"><i class="bi bi-inbox"></i>No students in this stage.</div>
                    @endforelse
                    @if ($counts[$stage] > $columnLimit)
                        <div class="kb-limit">Showing first {{ $columnLimit }} of {{ $counts[$stage] }} — narrow the filters to see more.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/kanban-board.js') }}"></script>
<script>
    KanbanBoard.init('pipelineBoard');
</script>
@endpush
