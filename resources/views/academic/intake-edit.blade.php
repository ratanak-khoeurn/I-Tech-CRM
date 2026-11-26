@extends('layouts.app')

@section('title', 'Edit Intake')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-box-arrow-in-down text-primary me-1"></i>Edit Intake</h1>
    <div class="page-subtitle">{{ $intake->name }}</div>
</div>

@include('academic._tabs')

<div class="card mx-auto">
    <div class="card-body">
        <form method="POST" action="{{ route('intakes.update', $intake) }}" data-loading>
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $intake->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $intake->code) }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Academic year</label>
                    <input type="number" min="1900" max="2999" name="academic_year" class="form-control" value="{{ old('academic_year', $intake->academic_year) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $intake->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $intake->end_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Capacity</label>
                    <input type="number" min="0" name="capacity" class="form-control" value="{{ old('capacity', $intake->capacity) }}">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $intake->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $intake->description) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Intake</button>
                <a href="{{ route('intakes.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
