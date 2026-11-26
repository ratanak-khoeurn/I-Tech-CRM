@extends('layouts.app')

@section('title', $batch->name)

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-diagram-3 text-primary me-1"></i>{{ $batch->name }}</h1>
        <div class="page-subtitle"><span class="badge text-bg-secondary">{{ $batch->code }}</span></div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('student-movements.create', ['student_id' => '']) }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-right me-1"></i>Record Movement</a>
        <a href="{{ route('batches.edit', $batch) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('batches.index') }}" class="btn btn-light">Back</a>
    </div>
</div>

@include('academic._tabs')

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Course</div><div class="fw-semibold">{{ $batch->course->name ?? '—' }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Campus</div><div class="fw-semibold">{{ $batch->campus->name ?? '—' }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Intake / Semester</div>
        <div class="fw-semibold">{{ $batch->intake->name ?? '—' }} · {{ $batch->semester->name ?? '—' }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Dates</div>
        <div class="fw-semibold">{{ $batch->start_date?->format('M d, Y') ?? '—' }} → {{ $batch->end_date?->format('M d, Y') ?? '—' }}</div>
    </div></div></div>
</div>

@if($batch->description)
    <div class="alert alert-light border">{{ $batch->description }}</div>
@endif

<div class="card">
    <div class="card-header bg-white fw-semibold">Enrolled Students ({{ $enrollments->total() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>Code</th><th>Registration #</th><th>Grade</th><th>Enrolled</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($enrollments as $sc)
                    <tr>
                        <td class="fw-semibold">{{ $sc->student->name_english ?? $sc->student->name_khmer ?? '—' }}</td>
                        <td>{{ $sc->student->student_code ?? '—' }}</td>
                        <td>{{ $sc->registration_number ?? '—' }}</td>
                        <td>{{ $sc->grade ?? '—' }}</td>
                        <td>{{ $sc->enrollment_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-end">
                            @if($sc->student)
                                <a href="{{ route('students.show', $sc->student) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state py-5"><i class="bi bi-people"></i><p class="mb-0">No students enrolled in this batch yet.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($enrollments->hasPages())
        <div class="card-footer bg-transparent">{{ $enrollments->links() }}</div>
    @endif
</div>
@endsection
