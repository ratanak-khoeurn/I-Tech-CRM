@extends('layouts.app')

@section('title', 'Edit Semester')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-calendar3 text-primary me-1"></i>Edit Semester</h1>
    <div class="page-subtitle">{{ $semester->name }}</div>
</div>

@include('academic._tabs')

<div class="card mx-auto">
    <div class="card-body">
        <form method="POST" action="{{ route('semesters.update', $semester) }}" data-loading>
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $semester->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $semester->code) }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Academic year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">— Select academic year —</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $semester->academic_year_id) === (string) $year->id)>
                                {{ $year->academic_year }} — {{ $year->campus?->name ?? 'Campus not set' }}{{ $year->courseCategory?->name_en ? ' · ' . $year->courseCategory->name_en : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Term #</label>
                    <input type="number" min="1" max="12" name="term" class="form-control" value="{{ old('term', $semester->term) }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $semester->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $semester->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $semester->end_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $semester->description) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Semester</button>
                <a href="{{ route('semesters.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
