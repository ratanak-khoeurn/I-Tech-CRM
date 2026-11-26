@extends('layouts.app')

@section('title', 'Batches')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-diagram-3 text-primary me-1"></i>Batches</h1>
        <div class="page-subtitle">Course cohorts tied to an intake, semester and campus</div>
    </div>
    <a href="{{ route('batches.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Batch</a>
</div>

@include('academic._tabs')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Batch name or code" value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Course</label>
                <select name="course_id" class="form-select">
                    <option value="">All</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected((int) request('course_id') === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">All</option>
                    @foreach($campuses as $c)
                        <option value="{{ $c->id }}" @selected((int) request('campus_id') === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $k => $label)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('batches.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch</th><th>Code</th><th>Course</th><th>Campus</th>
                    <th>Intake</th><th>Semester</th><th>Study Time</th><th class="text-center">Students</th>
                    <th class="text-center">Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('batches.show', $batch) }}" class="text-decoration-none">{{ $batch->name }}</a></td>
                        <td><span class="badge text-bg-secondary">{{ $batch->code }}</span></td>
                        <td>{{ $batch->course->name ?? '—' }}</td>
                        <td>{{ $batch->campus->name ?? '—' }}</td>
                        <td>{{ $batch->intake->name ?? '—' }}</td>
                        <td>{{ $batch->semester->name ?? '—' }}</td>
                        <td>{{ $batch->study_time ?: '—' }}</td>
                        <td class="text-center">{{ $batch->student_courses_count }}</td>
                        <td class="text-center">
                            @php $badge = ['upcoming'=>'info','ongoing'=>'success','completed'=>'secondary','cancelled'=>'danger'][$batch->status] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $badge }}">{{ $batch->statusLabel() }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('batches.destroy', $batch) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **batch** {{ $batch->name }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">
                        <div class="empty-state py-5"><i class="bi bi-diagram-3"></i><p class="mb-0">No batches yet. Add your first batch.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches->hasPages())
        <div class="card-footer bg-transparent">{{ $batches->links() }}</div>
    @endif
</div>
@endsection
