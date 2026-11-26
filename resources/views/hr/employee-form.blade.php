
@extends('layouts.app')

@section('title', $employee->exists ? 'Edit Employee' : 'Add Employee')

@section('content')
<div class="mb-3">
    <h1 class="page-title">
        <i class="bi bi-person-vcard text-primary me-1"></i>
        {{ $employee->exists ? 'Edit Employee' : 'Add Employee' }}
    </h1>

    <div class="page-subtitle">
        {{ $employee->exists ? $employee->fullName() : 'Create a new staff record' }}
    </div>
</div>

@include('hr._tabs')

<form method="POST"
      enctype="multipart/form-data"
      action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}"
      data-loading>

    @csrf

    @if($employee->exists)
        @method('PUT')
    @endif

    <div class="row g-3">

        {{-- Employee photo --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-camera me-1"></i> Employee Photo
                </div>

                <div class="card-body text-center">

                    {{-- Photo preview --}}
                    <div class="mb-3">
                        @if($employee->photo)
                            <img
                                id="photoPreview"
                                src="{{ asset('storage/' . $employee->photo) }}"
                                alt="Employee photo"
                                class="rounded-circle border"
                                width="120"
                                height="120"
                                style="object-fit: cover;"
                            >
                        @else
                            <div
                                id="photoPlaceholder"
                                class="avatar-circle mx-auto"
                                style="width:120px;height:120px;font-size:2.5rem;"
                            >
                                {{ strtoupper(substr($employee->first_name ?: 'E', 0, 1)) }}
                            </div>

                            <img
                                id="photoPreview"
                                src=""
                                alt="New employee photo preview"
                                class="rounded-circle border d-none"
                                width="120"
                                height="120"
                                style="object-fit: cover;"
                            >
                        @endif
                    </div>

                    <label for="photo" class="form-label d-block text-start">
                        {{ $employee->photo ? 'Change photo' : 'Upload photo' }}
                    </label>

                    <input
                        type="file"
                        name="photo"
                        id="photo"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="form-control @error('photo') is-invalid @enderror"
                    >

                    @error('photo')
                        <div class="invalid-feedback d-block text-start">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text text-start">
                        JPG, PNG or WebP. Maximum size: 2 MB.
                        Leave empty to keep the current photo.
                    </div>

                    <button
                        type="button"
                        id="removeSelectedPhoto"
                        class="btn btn-sm btn-outline-secondary mt-2 d-none"
                    >
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Undo photo selection
                    </button>

                </div>
            </div>
        </div>

        {{-- Employee information --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-person-lines-fill me-1"></i>
                    Employee Information
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                First name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="first_name"
                                class="form-control @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name', $employee->first_name) }}"
                                required
                            >
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last name</label>
                            <input
                                type="text"
                                name="last_name"
                                class="form-control @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name', $employee->last_name) }}"
                            >
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Employee code</label>
                            <input
                                type="text"
                                name="employee_code"
                                class="form-control @error('employee_code') is-invalid @enderror"
                                value="{{ old('employee_code', $employee->employee_code) }}"
                                placeholder="Auto-generated if blank"
                            >
                            @error('employee_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Linked login account</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">— None —</option>
                                @foreach($users as $u)
                                    <option
                                        value="{{ $u->id }}"
                                        @selected((string) old('user_id', $employee->user_id) === (string) $u->id)
                                    >
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <select name="position_id" class="form-select @error('position_id') is-invalid @enderror">
                                <option value="">— None —</option>
                                @foreach($positions as $p)
                                    <option
                                        value="{{ $p->id }}"
                                        @selected((string) old('position_id', $employee->position_id) === (string) $p->id)
                                    >
                                        {{ $p->title }}{{ $p->department ? ' · ' . $p->department : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Campus</label>
                            <select name="campus_id" class="form-select @error('campus_id') is-invalid @enderror">
                                <option value="">— None —</option>
                                @foreach($campuses as $c)
                                    <option
                                        value="{{ $c->id }}"
                                        @selected((string) old('campus_id', $employee->campus_id) === (string) $c->id)
                                    >
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $employee->email) }}"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $employee->phone) }}"
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $k => $label)
                                    <option
                                        value="{{ $k }}"
                                        @selected(old('gender', $employee->gender) === $k)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date of birth</label>
                            <input
                                type="date"
                                name="date_of_birth"
                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"
                            >
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Hire date</label>
                            <input
                                type="date"
                                name="hire_date"
                                class="form-control @error('hire_date') is-invalid @enderror"
                                value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                            >
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Employment type <span class="text-danger">*</span>
                            </label>
                            <select
                                name="employment_type"
                                class="form-select @error('employment_type') is-invalid @enderror"
                                required
                            >
                                @foreach($types as $k => $label)
                                    <option
                                        value="{{ $k }}"
                                        @selected(old('employment_type', $employee->employment_type ?? 'full_time') === $k)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required
                            >
                                @foreach($statuses as $k => $label)
                                    <option
                                        value="{{ $k }}"
                                        @selected(old('status', $employee->status ?? 'active') === $k)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract end</label>
                            <input
                                type="date"
                                name="contract_end_date"
                                class="form-control @error('contract_end_date') is-invalid @enderror"
                                value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}"
                            >
                            @error('contract_end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($canViewSensitive)
                            <div class="col-md-4">
                                <label class="form-label">
                                    Salary ($)
                                    <i class="bi bi-lock text-muted" title="Confidential"></i>
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="salary"
                                    class="form-control @error('salary') is-invalid @enderror"
                                    value="{{ old('salary', $employee->salary) }}"
                                >
                                @error('salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input
                                type="text"
                                name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $employee->address) }}"
                            >
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                class="form-control @error('notes') is-invalid @enderror"
                                rows="3"
                            >{{ old('notes', $employee->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form actions --}}
    <div class="d-flex gap-2 mt-3 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            {{ $employee->exists ? 'Update Employee' : 'Create Employee' }}
        </button>

        <a
            href="{{ $employee->exists ? route('employees.show', $employee) : route('employees.index') }}"
            class="btn btn-light"
        >
            Cancel
        </a>
    </div>
</form>

{{-- Photo preview --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const undoButton = document.getElementById('removeSelectedPhoto');

    if (!photoInput || !photoPreview) {
        return;
    }

    // Preserve the current photo so selection can be undone.
    const originalPhoto = photoPreview.getAttribute('src') || '';
    const originalHasPhoto = originalPhoto !== '';
    let previewUrl = null;

    photoInput.addEventListener('change', function () {
        const file = this.files && this.files[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            this.value = '';
            alert('Please select a valid image file.');
            return;
        }

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        previewUrl = URL.createObjectURL(file);

        photoPreview.src = previewUrl;
        photoPreview.classList.remove('d-none');

        if (photoPlaceholder) {
            photoPlaceholder.classList.add('d-none');
        }

        undoButton.classList.remove('d-none');
    });

    undoButton.addEventListener('click', function () {
        photoInput.value = '';

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (originalHasPhoto) {
            photoPreview.src = originalPhoto;
            photoPreview.classList.remove('d-none');
        } else {
            photoPreview.src = '';
            photoPreview.classList.add('d-none');

            if (photoPlaceholder) {
                photoPlaceholder.classList.remove('d-none');
            }
        }

        undoButton.classList.add('d-none');
    });
});
</script>
@endsection
