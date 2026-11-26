@extends('layouts.app')

@section('title', 'Positions')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-person-rolodex text-primary me-1"></i>Positions</h1>
        <div class="page-subtitle">Job titles and departments for employees</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#positionModal"><i class="bi bi-plus-lg me-1"></i>Add Position</button>
</div>

@include('hr._tabs')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Title or department" value="{{ request('q') }}">
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
                <a href="{{ route('positions.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>Department</th><th class="text-center">Level</th>
                    <th class="text-center">Employees</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($positions as $position)
                    <tr>
                        <td class="fw-semibold">{{ $position->title }}</td>
                        <td>{{ $position->department ?: '—' }}</td>
                        <td class="text-center">{{ $position->level ?? '—' }}</td>
                        <td class="text-center">{{ $position->employees_count }}</td>
                        <td class="text-center">
                            @if($position->is_active)<span class="badge text-bg-success">Active</span>@else<span class="badge text-bg-secondary">Inactive</span>@endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('positions.edit', $position) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('positions.destroy', $position) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **position** {{ $position->title }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state py-5"><i class="bi bi-person-rolodex"></i><p class="mb-0">No positions yet. Add your first position.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($positions->hasPages())
        <div class="card-footer bg-transparent">{{ $positions->links() }}</div>
    @endif
</div>

<div class="modal fade" id="positionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('positions.store') }}" class="modal-content" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Admissions Officer" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="e.g. Administration">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Level</label>
                        <input type="number" min="1" max="99" name="level" class="form-control" value="{{ old('level') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Position</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('positionModal')).show(); });</script>
    @endpush
@endif
@endsection
