@extends('layouts.app')

@section('title', 'Fee Setup')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-sliders text-primary me-1"></i>Fee Setup</h1>
        <div class="page-subtitle">Standard fees per course used when building invoices</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.create') }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>New Invoice</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feeModal"><i class="bi bi-plus-lg me-1"></i>Add Fee</button>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('fee-structures.index') }}">Fee Setup</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('payments.index') }}">Payments</a></li>
</ul>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Fee name" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Course</label>
                <select name="course_id" class="form-select">
                    <option value="">All courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected((int) request('course_id') === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Fee type</label>
                <select name="fee_type" class="form-select">
                    <option value="">All types</option>
                    @foreach(\App\Models\FeeStructure::FEE_TYPES as $k => $label)
                        <option value="{{ $k }}" @selected(request('fee_type') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('fee-structures.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fee name</th><th>Course</th><th>Campus</th><th>Type</th>
                    <th class="text-end">Amount</th><th>Frequency</th><th class="text-center">Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fees as $fee)
                    <tr>
                        <td class="fw-semibold">{{ $fee->name }}</td>
                        <td>{{ $fee->course->name ?? '—' }}</td>
                        <td>{{ $fee->campus->name ?? 'All campuses' }}</td>
                        <td>{{ \App\Models\FeeStructure::FEE_TYPES[$fee->fee_type] ?? $fee->fee_type }}</td>
                        <td class="text-end">${{ number_format((float) $fee->amount, 2) }}</td>
                        <td>{{ \App\Models\FeeStructure::FREQUENCIES[$fee->frequency] ?? $fee->frequency }}</td>
                        <td class="text-center">
                            @if($fee->is_active)<span class="badge text-bg-success">Active</span>@else<span class="badge text-bg-secondary">Inactive</span>@endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('fee-structures.edit', $fee) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('fee-structures.destroy', $fee) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **fee** {{ $fee->name }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        <div class="empty-state py-5"><i class="bi bi-sliders"></i><p class="mb-0">No fee structures yet. Add your first fee.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($fees->hasPages())
        <div class="card-footer bg-transparent">{{ $fees->links() }}</div>
    @endif
</div>

<div class="modal fade" id="feeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('fee-structures.store') }}" class="modal-content" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Fee name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Tuition — Web Development" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                            <option value="">Select…</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" @selected((int) old('course_id') === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">Campus</label>
                        <select name="campus_id" class="form-select">
                            <option value="">All campuses</option>
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}" @selected((int) old('campus_id') === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Fee type <span class="text-danger">*</span></label>
                        <select name="fee_type" class="form-select" required>
                            @foreach(\App\Models\FeeStructure::FEE_TYPES as $k => $label)
                                <option value="{{ $k }}" @selected(old('fee_type', 'tuition') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select name="frequency" class="form-select" required>
                            @foreach(\App\Models\FeeStructure::FREQUENCIES as $k => $label)
                                <option value="{{ $k }}" @selected(old('frequency', 'one_time') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Fee</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('feeModal')).show();
        });
    </script>
    @endpush
@endif
@endsection
