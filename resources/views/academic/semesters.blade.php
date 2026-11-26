@extends('layouts.app')

@section('title', 'Semesters')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar3 text-primary me-1"></i>Semesters</h1>
        <div class="page-subtitle">Academic terms that batches run within</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#semesterModal"><i class="bi bi-plus-lg me-1"></i>Add Semester</button>
</div>

@include('academic._tabs')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Name or code" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('semesters.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Code</th><th class="text-center">Year</th><th class="text-center">Term</th><th>Dates</th>
                    <th class="text-center">Batches</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($semesters as $semester)
                    <tr>
                        <td class="fw-semibold">{{ $semester->name }}</td>
                        <td><span class="badge text-bg-secondary">{{ $semester->code }}</span></td>
                        <td class="text-center">{{ $semester->academicYear?->academic_year ?? '—' }}</td>
                        <td class="text-center">{{ $semester->term ?? '—' }}</td>
                        <td>{{ $semester->start_date?->format('M d, Y') ?? '—' }} → {{ $semester->end_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-center">{{ $semester->batches_count }}</td>
                        <td class="text-center">
                            @if($semester->is_active)<span class="badge text-bg-success">Active</span>@else<span class="badge text-bg-secondary">Inactive</span>@endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('semesters.edit', $semester) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('semesters.destroy', $semester) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **semester** {{ $semester->name }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state py-5"><i class="bi bi-calendar3"></i><p class="mb-0">No semesters yet. Add your first semester.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($semesters->hasPages())
        <div class="card-footer bg-transparent">{{ $semesters->links() }}</div>
    @endif
</div>

<div class="modal fade" id="semesterModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('semesters.store') }}" class="modal-content" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Semester 1" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="2026-S1" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">Academic year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">— Select academic year —</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" @selected((string) old('academic_year_id') === (string) $year->id)>
                                    {{ $year->academic_year }} — {{ $year->campus?->name ?? 'Campus not set' }}{{ $year->courseCategory?->name_en ? ' · ' . $year->courseCategory->name_en : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Term #</label>
                        <input type="number" min="1" max="12" name="term" class="form-control" value="{{ old('term') }}">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Start date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">End date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Semester</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('semesterModal')).show(); });</script>
    @endpush
@endif
@endsection
