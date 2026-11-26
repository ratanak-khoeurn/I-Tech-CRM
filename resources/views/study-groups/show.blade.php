@extends('layouts.app')

@section('title', $studyGroup->name)

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-people-fill text-primary me-1"></i>{{ $studyGroup->name }}</h1>
        <div class="page-subtitle">
            @if($studyGroup->code){{ $studyGroup->code }} · @endif
            {{ $studyGroup->campus?->name ?? 'Campus not set' }} ·
            {{ $studyGroup->courseCategory?->name_en ?? 'Category not set' }} ·
            {{ $studyGroup->academicYear?->academic_year ?? 'Academic year not set' }} ·
            {{ $studyGroup->program?->name ?? 'Program not set' }} ·
            {{ $studyGroup->intake?->name ?? 'Intake not set' }} ·
            Teacher: {{ $studyGroup->teacher?->fullName() ?? 'Not assigned' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('study-groups.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>All Groups</a>
        <a href="{{ route('study-groups.edit', $studyGroup) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit Group</a>
    </div>
</div>

@if($studyGroup->description)
    <div class="card mb-3"><div class="card-body py-3">{{ $studyGroup->description }}</div></div>
@endif

<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="rounded-3 bg-primary-subtle text-primary p-3"><i class="bi bi-people fs-4"></i></span>
            <div><div class="text-muted small">Group Members</div><div class="fs-4 fw-bold">{{ $studyGroup->students()->count() }}</div></div>
        </div></div>
    </div>
    <div class="col-sm-6">
        <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="rounded-3 bg-success-subtle text-success p-3"><i class="bi bi-mortarboard fs-4"></i></span>
            <div><div class="text-muted small">Graduated</div><div class="fs-4 fw-bold">{{ $studyGroup->students()->where('graduation_status', 'graduated')->count() }}</div></div>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0 fw-bold"><i class="bi bi-person-plus me-1 text-primary"></i>Add Current Students</h2></div>
    <div class="card-body">
        <form method="GET" action="{{ route('study-groups.show', $studyGroup) }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-9">
                <label class="form-label small text-muted mb-1" for="student_q">Search students by name or student code</label>
                <input type="search" id="student_q" name="student_q" class="form-control" value="{{ request('student_q') }}" placeholder="Name or student code">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                <a href="{{ route('study-groups.show', $studyGroup) }}" class="btn btn-light" title="Clear search"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        @if($availableStudents->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Student</th><th>Code</th><th>Course</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @foreach($availableStudents as $student)
                            <tr>
                                <td><a href="{{ route('students.show', $student) }}" class="text-decoration-none fw-semibold">{{ $student->name_english ?: $student->name_khmer }}</a></td>
                                <td>{{ $student->student_code }}</td>
                                <td>{{ $student->studentCourses->sortByDesc('id')->first()?->course?->name ?? '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('study-groups.members.store', $studyGroup) }}">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Add</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($availableStudents->hasPages())<div class="pt-3">{{ $availableStudents->links() }}</div>@endif
        @else
            <div class="text-muted small">No eligible students found. Search for another student or check whether they are already in a group.</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><h2 class="h6 mb-0 fw-bold">Group Students</h2><small class="text-muted">Select one or more eligible students to record their graduation.</small></div>
    </div>

    <div class="card-body border-bottom">
        <form id="graduateSelectedForm" method="POST" action="{{ route('study-groups.graduate', $studyGroup) }}" onsubmit="return confirm('You are about to mark the selected students as graduated. This updates their lifecycle stage and education status. Would you like to proceed?');">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small mb-1">Graduation date</label>
                    <input type="date" name="graduation_date" class="form-control" value="{{ old('graduation_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small mb-1">Graduation year</label>
                    <input type="number" name="graduation_year" class="form-control" value="{{ old('graduation_year', $studyGroup->graduation_year) }}" min="1980" max="2100" placeholder="Uses group year or date">
                </div>
                <div class="col-sm-4 col-md-6">
                    <button class="btn btn-success"><i class="bi bi-mortarboard me-1"></i>Graduate Selected Students</button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th style="width: 44px;"><input type="checkbox" class="form-check-input" id="selectAllStudents" aria-label="Select all ungraduated students"></th><th>Student</th><th>Course</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse($members as $student)
                    @php $enrollment = $student->studentCourses->sortByDesc('id')->first(); @endphp
                    <tr>
                        <td>
                            @if($student->graduation_status !== 'graduated')
                                <input type="checkbox" class="form-check-input graduate-student-checkbox" name="student_ids[]" value="{{ $student->id }}" form="graduateSelectedForm" aria-label="Select {{ $student->name_english }} for graduation">
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('students.show', $student) }}" class="fw-semibold text-decoration-none">{{ $student->name_english ?: $student->name_khmer }}</a>
                            <div class="text-muted small">{{ $student->student_code }}</div>
                        </td>
                        <td>{{ $enrollment?->course?->name ?? '—' }}</td>
                        <td>
                            @if($student->graduation_status === 'graduated')
                                <span class="badge text-bg-success">Graduated</span>
                            @else
                                <span class="badge text-bg-primary">Current Student</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('study-groups.members.destroy', [$studyGroup, $student]) }}" onsubmit="return confirm('You are about to remove this student from the **study group**. Would you like to proceed?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Remove from group"><i class="bi bi-person-dash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state py-5"><i class="bi bi-people"></i><p class="mb-0">No students in this group yet. Add students above to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($members->hasPages())<div class="card-footer bg-transparent">{{ $members->links() }}</div>@endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllStudents');
    const studentCheckboxes = document.querySelectorAll('.graduate-student-checkbox');

    selectAll?.addEventListener('change', function () {
        studentCheckboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });
});
</script>
@endpush
@endsection
