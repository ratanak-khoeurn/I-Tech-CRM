@extends('layouts.app')

@section('title', 'Edit Courses')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-book text-primary me-1"></i>Edit Course</h1>
        <div class="page-subtitle">{{ $course->name }}</div>
    </div>
</div>

@include('academic._tabs')

<div class="card mx-auto" style="max-width: 700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('courses.update', $course) }}" data-loading>
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $course->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $course->code) }}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Program</label>
                <select name="program_id" class="form-select">
                    <option value="">— Select program —</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected((int) old('program_id', $course->program_id) === $program->id)>{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Course Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— Select category —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $course->category_id) === $category->id)>
                            {{ $category->name_en }}{{ $category->extra_course ? ' (Extra / Supplementary)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Categories flagged as "Extra" become Supplementary Courses.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Course category (free text)</label>
                <input type="text" name="course_category" class="form-control" value="{{ old('course_category', $course->course_category) }}" placeholder="e.g. Web Development">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $course->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-award text-warning me-1"></i>Certificate Content</label>
                <textarea name="certificate_text" class="form-control" rows="4"
                    placeholder="Has successfully completed the {{ '{course}' }} at {{ '{center}' }}.">{{ old('certificate_text', $course->certificate_text) }}</textarea>
                <div class="form-text">
                    This wording appears on the printed Certificate of Completion for this course. Leave blank to fall back to the
                    course name and description. You may use <code>{course}</code> and <code>{center}</code> as placeholders.
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Duration (months)</label>
                <input type="number" name="duration_months" class="form-control" min="1" max="120" value="{{ old('duration_months', $course->duration_months) }}">
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $course->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Course</button>
                <a href="{{ route('courses.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
