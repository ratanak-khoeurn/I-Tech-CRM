@extends('layouts.app')

@section('title', 'Courses')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-book text-primary me-1"></i>Courses</h1>
        <div class="page-subtitle">Manage academic Course.</div>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal"><i class="bi bi-plus-lg me-1"></i>Add Course</button>
</div>

@include('academic._tabs')

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Program</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-center">Duration</th>
                    <th class="text-center">Student Records</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td class="fw-semibold">{{ $course->name }}</td>
                        <td><span class="badge text-bg-secondary">{{ $course->code }}</span></td>
                        <td>{{ $course->program?->name ?? '—' }}</td>
                        <td>
                            {{ $course->category?->name_en ?? ($course->course_category ?: '—') }}
                            @if($course->category?->extra_course)
                                <span class="badge text-bg-info ms-1" style="font-size:.6rem">Extra</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ \Illuminate\Support\Str::limit($course->description, 60) ?: '—' }}</td>
                        <td class="text-center">{{ $course->duration_months ? $course->duration_months . ' mo' : '—' }}</td>
                        <td class="text-center">{{ $course->student_courses_count }}</td>
                        <td class="text-center">
                            @if($course->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('courses.destroy', $course) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **course** {{ $course->name }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        <div class="empty-state py-5"><i class="bi bi-book"></i><p class="mb-0">No courses yet. Add your first course.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($courses->hasPages())
        <div class="card-footer bg-transparent">{{ $courses->links() }}</div>
    @endif
</div>

<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('courses.store') }}" class="modal-content" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. WEB" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select">
                        <option value="">— Select program —</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected((int) old('program_id') === $program->id)>{{ $program->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Manage programs in the Academic → Programs tab.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Course Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">— Select category —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>
                                {{ $category->name_en }}{{ $category->extra_course ? ' (Extra / Supplementary)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Categories flagged as "Extra" become Supplementary Courses. Manage them in Academic → Course Categories.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Course category (free text)</label>
                    <input type="text" name="course_category" class="form-control" value="{{ old('course_category') }}" placeholder="e.g. Web Development">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-award text-warning me-1"></i>Certificate Content</label>
                    <textarea name="certificate_text" class="form-control" rows="4"
                        placeholder="Has successfully completed the {course} at {center}.">{{ old('certificate_text') }}</textarea>
                    <div class="form-text">Shown on this course's Certificate of Completion. Blank falls back to name + description. Placeholders: <code>{course}</code>, <code>{center}</code>.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Duration (months)</label>
                    <input type="number" name="duration_months" class="form-control" min="1" max="120" value="{{ old('duration_months') }}">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Course</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var m = new bootstrap.Modal(document.getElementById('courseModal'));
            m.show();
        });
    </script>
    @endpush
@endif
@endsection
