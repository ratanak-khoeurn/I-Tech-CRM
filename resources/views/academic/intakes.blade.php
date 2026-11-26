@extends('layouts.app')

@section('title', 'Intakes')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-box-arrow-in-down text-primary me-1"></i>Intakes</h1>
        <div class="page-subtitle">Admission rounds / enrollment periods</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#intakeModal"><i class="bi bi-plus-lg me-1"></i>Add Intake</button>
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
                <a href="{{ route('intakes.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Code</th><th class="text-center">Year</th><th>Dates</th>
                    <th class="text-center">Capacity</th><th class="text-center">Batches</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($intakes as $intake)
                    <tr>
                        <td class="fw-semibold">{{ $intake->name }}</td>
                        <td><span class="badge text-bg-secondary">{{ $intake->code }}</span></td>
                        <td class="text-center">{{ $intake->academic_year ?? '—' }}</td>
                        <td>{{ $intake->start_date?->format('M d, Y') ?? '—' }} → {{ $intake->end_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-center">{{ $intake->capacity ?? '—' }}</td>
                        <td class="text-center">{{ $intake->batches_count }}</td>
                        <td class="text-center">
                            @if($intake->is_active)<span class="badge text-bg-success">Active</span>@else<span class="badge text-bg-secondary">Inactive</span>@endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('intakes.edit', $intake) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('intakes.destroy', $intake) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **intake** {{ $intake->name }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state py-5"><i class="bi bi-box-arrow-in-down"></i><p class="mb-0">No intakes yet. Add your first intake.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($intakes->hasPages())
        <div class="card-footer bg-transparent">{{ $intakes->links() }}</div>
    @endif
</div>

<div class="modal fade" id="intakeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('intakes.store') }}" class="modal-content" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Intake</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Fall Intake" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="FALL-2026" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">Academic year</label>
                        <input type="number" min="1900" max="2999" name="academic_year" class="form-control" value="{{ old('academic_year') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Start date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">End date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Capacity</label>
                        <input type="number" min="0" name="capacity" class="form-control" value="{{ old('capacity') }}">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Intake</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('intakeModal')).show(); });</script>
    @endpush
@endif
@endsection
