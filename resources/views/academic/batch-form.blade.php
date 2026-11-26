@extends('layouts.app')

@section('title', $batch->exists ? 'Edit Batch' : 'Add Batch')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-diagram-3 text-primary me-1"></i>{{ $batch->exists ? 'Edit Batch' : 'Add Batch' }}</h1>
    <div class="page-subtitle">{{ $batch->exists ? $batch->name : 'Create a new course cohort' }}</div>
</div>

@include('academic._tabs')

<div class="card mx-auto">
    <div class="card-body">
        <form method="POST" action="{{ $batch->exists ? route('batches.update', $batch) : route('batches.store') }}" data-loading>
            @csrf
            @if($batch->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Batch name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $batch->name) }}" placeholder="e.g. Web Dev — Batch 2026-A" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $batch->code) }}" placeholder="WEB-2026-A" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" @selected((int) old('course_id', $batch->course_id) === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Campus</label>
                    <select name="campus_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($campuses as $c)
                            <option value="{{ $c->id }}" @selected((int) old('campus_id', $batch->campus_id) === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Intake</label>
                    <select name="intake_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($intakes as $i)
                            <option value="{{ $i->id }}" @selected((int) old('intake_id', $batch->intake_id) === $i->id)>{{ $i->name }} ({{ $i->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($semesters as $s)
                            <option value="{{ $s->id }}" @selected((int) old('semester_id', $batch->semester_id) === $s->id)>{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Study time</label>
                    <input type="text" name="study_time" class="form-control" value="{{ old('study_time', $batch->study_time) }}" placeholder="e.g. Morning, Afternoon, 6:00–8:00 PM">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date', $batch->start_date?->format('Y-m-d')) }}">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">End date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date', $batch->end_date?->format('Y-m-d')) }}">
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Capacity</label>
                    <input type="number" min="0" name="capacity" class="form-control" value="{{ old('capacity', $batch->capacity) }}" placeholder="Optional">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        @foreach($statuses as $k => $label)
                            <option value="{{ $k }}" @selected(old('status', $batch->status ?? 'upcoming') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $batch->description) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ $batch->exists ? 'Update Batch' : 'Create Batch' }}</button>
                <a href="{{ route('batches.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
