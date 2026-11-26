@extends('layouts.app')

@section('title', 'Edit Position')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-person-rolodex text-primary me-1"></i>Edit Position</h1>
    <div class="page-subtitle">{{ $position->title }}</div>
</div>

@include('hr._tabs')

<div class="card mx-auto" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="{{ route('positions.update', $position) }}" data-loading>
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $position->title) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $position->department) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Level</label>
                    <input type="number" min="1" max="99" name="level" class="form-control" value="{{ old('level', $position->level) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $position->description) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $position->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Position</button>
                <a href="{{ route('positions.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
