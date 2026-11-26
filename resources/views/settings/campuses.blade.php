@extends('layouts.app')

@section('title', 'Campuses')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-mortarboard text-primary me-1"></i>Campuses / Branches</h1>
        <div class="page-subtitle">Manage education center locations.</div>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#campusModal">
        <i class="bi bi-plus-lg me-1"></i>Add Campus
    </button>
</div>

@include('academic._tabs')

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th class="text-center">Student Records</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campuses as $campus)
                    <tr>
                        <td class="fw-semibold">
                            <div class="d-flex align-items-center gap-2">
                                @if($campus->image)
                                    <img src="{{ asset('storage/' . $campus->image) }}" alt="{{ $campus->name }}" class="rounded border" width="40" height="30" style="object-fit: cover;">
                                @else
                                    <span class="rounded border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width:40px;height:30px;"><i class="bi bi-image"></i></span>
                                @endif
                                {{ $campus->name }}
                            </div>
                        </td>
                        <td><span class="badge text-bg-secondary">{{ $campus->code }}</span></td>
                        <td>{{ $campus->address ?: '—' }}</td>
                        <td>{{ $campus->phone ?: '—' }}</td>
                        <td class="text-center">{{ $campus->student_courses_count }}</td>
                        <td class="text-center">
                            @if($campus->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('campuses.edit', $campus) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('campuses.destroy', $campus) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **campus** {{ $campus->name }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state py-5"><i class="bi bi-geo-alt"></i><p class="mb-0">No campuses yet. Add your first campus.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campuses->hasPages())
        <div class="card-footer bg-transparent">{{ $campuses->links() }}</div>
    @endif
</div>

<div class="modal fade" id="campusModal" tabindex="-1" aria-labelledby="campusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('campuses.store') }}" class="modal-content" enctype="multipart/form-data" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="campusModalLabel">Add Campus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. PP" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Campus Photo</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">JPG/PNG/WEBP, max 2 MB.</div>
                    @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Campus</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var m = new bootstrap.Modal(document.getElementById('campusModal'));
            m.show();
        });
    </script>
    @endpush
@endif
@endsection
