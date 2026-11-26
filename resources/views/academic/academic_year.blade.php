@extends('layouts.app')

@section('title', 'Academic Years')

@section('content')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">
            <i class="bi bi-calendar3 text-primary me-1"></i>
            Academic Years
        </h1>
        <div class="page-subtitle">
            Manage academic years, periods and current academic year
        </div>
    </div>

    <button type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addAcademicYearModal">
        <i class="bi bi-plus-lg me-1"></i>
        Add Academic Year
    </button>
</div>

@include('academic._tabs')

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('academic-years.index') }}"
              class="row g-2 align-items-end">

            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">
                    Search
                </label>
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="Academic year..."
                       value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">
                    Campus
                </label>

                <select name="campus_id" class="form-select">
                    <option value="">All Campuses</option>

                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}"
                            @selected((int) request('campus_id') === $campus->id)>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">
                    Current Year
                </label>

                <select name="current_year" class="form-select">
                    <option value="">All</option>
                    <option value="1"
                        @selected(request('current_year') === '1')}>
                        Current
                    </option>
                    <option value="0"
                        @selected(request('current_year') === '0')}>
                        Previous
                    </option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>
                    Filter
                </button>

                <a href="{{ route('academic-years.index') }}"
                   class="btn btn-light"
                   title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th>Academic Year</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Campus</th>
                    <th>Course Category</th>
                    <th class="text-center">Current</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($academicYears as $year)

                <tr>

                    <td class="fw-semibold">
                        {{ $year->academic_year }}
                    </td>

                    <td>
                        {{ $year->from?->format('d M Y') ?? '—' }}
                    </td>

                    <td>
                        {{ $year->to?->format('d M Y') ?? '—' }}
                    </td>

                    <td>
                        {{ $year->campus->name ?? '—' }}
                    </td>

                    <td>
                        {{ $year->courseCategory->name_en ?? '—' }}
                    </td>

                    <td class="text-center">

                        @if($year->current_year)

                            <span class="badge text-bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Current
                            </span>

                        @else

                            <span class="badge text-bg-secondary">
                                Previous
                            </span>

                        @endif

                    </td>

                    <td class="text-end text-nowrap">

                        {{-- Edit --}}
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                title="Edit"
                                data-bs-toggle="modal"
                                data-bs-target="#editAcademicYearModal{{ $year->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>

                        {{-- Delete --}}
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                title="Delete"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteAcademicYearModal{{ $year->id }}">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>

                {{-- ================= EDIT MODAL ================= --}}
                <div class="modal fade"
                     id="editAcademicYearModal{{ $year->id }}"
                     tabindex="-1"
                     aria-hidden="true">

                    <div class="modal-dialog modal-lg modal-dialog-centered">

                        <div class="modal-content">

                            <form method="POST"
                                  action="{{ route('academic-years.update', $year->id) }}">

                                @csrf
                                @method('PUT')

                                <div class="modal-header">

                                    <h5 class="modal-title">
                                        <i class="bi bi-pencil-square text-primary me-1"></i>
                                        Edit Academic Year
                                    </h5>

                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"></button>

                                </div>

                                <div class="modal-body">

                                    <div class="row g-3">

                                        {{-- Academic Year --}}
                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Academic Year <span class="text-danger">*</span>
                                            </label>

                                            <input type="text"
                                                   name="academic_year"
                                                   class="form-control"
                                                   value="{{ $year->academic_year }}"
                                                   placeholder="e.g. 2026-2027"
                                                   required>

                                        </div>

                                        {{-- Campus --}}
                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Campus <span class="text-danger">*</span>
                                            </label>

                                            <select name="campus_id"
                                                    class="form-select"
                                                    required>

                                                <option value="">
                                                    Select Campus
                                                </option>

                                                @foreach($campuses as $campus)

                                                    <option value="{{ $campus->id }}"
                                                        @selected($year->campus_id == $campus->id)>
                                                        {{ $campus->name }}
                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        {{-- From --}}
                                        <div class="col-md-6">

                                            <label class="form-label">
                                                From <span class="text-danger">*</span>
                                            </label>

                                            <input type="date"
                                                   name="from"
                                                   class="form-control"
                                                   value="{{ $year->from?->format('Y-m-d') }}"
                                                   required>

                                        </div>

                                        {{-- To --}}
                                        <div class="col-md-6">

                                            <label class="form-label">
                                                To <span class="text-danger">*</span>
                                            </label>

                                            <input type="date"
                                                   name="to"
                                                   class="form-control"
                                                   value="{{ $year->to?->format('Y-m-d') }}"
                                                   required>

                                        </div>

                                        {{-- Course Category --}}
                                        <div class="col-md-12">

                                            <label class="form-label">
                                                Course Category
                                                <span class="text-muted small">(optional)</span>
                                            </label>

                                            <select name="course_category_id"
                                                    class="form-select">

                                                <option value="">
                                                    None — set later
                                                </option>

                                                @foreach($categories as $category)

                                                    <option value="{{ $category->id }}"
                                                        @selected($year->course_category_id == $category->id)>
                                                        {{ $category->name_en }}
                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        {{-- Current --}}
                                        <div class="col-md-12">

                                            <div class="form-check form-switch">

                                                <input type="checkbox"
                                                       name="current_year"
                                                       value="1"
                                                       class="form-check-input"
                                                       id="editCurrent{{ $year->id }}"
                                                       @checked($year->current_year)>

                                                <label class="form-check-label"
                                                       for="editCurrent{{ $year->id }}">
                                                    Set as Current Academic Year
                                                </label>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button type="button"
                                            class="btn btn-light"
                                            data-bs-dismiss="modal">
                                        Cancel
                                    </button>

                                    <button type="submit"
                                            class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i>
                                        Update
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>


                {{-- ================= DELETE MODAL ================= --}}
                <div class="modal fade"
                     id="deleteAcademicYearModal{{ $year->id }}"
                     tabindex="-1"
                     aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Delete Academic Year
                                </h5>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <p class="mb-2">
                                    Are you sure you want to delete:
                                </p>

                                <strong>
                                    {{ $year->academic_year }}
                                </strong>

                                <p class="text-muted small mt-2 mb-0">
                                    This action cannot be undone.
                                </p>

                            </div>

                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-light"
                                        data-bs-dismiss="modal">
                                    Cancel
                                </button>

                                <form method="POST"
                                      action="{{ route('academic-years.destroy', $year->id) }}">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger">
                                        <i class="bi bi-trash me-1"></i>
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <tr>

                    <td colspan="7">

                        <div class="empty-state py-5">

                            <i class="bi bi-calendar3"></i>

                            <p class="mb-0">
                                No academic years yet.
                                Add your first academic year.
                            </p>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    @if($academicYears->hasPages())

        <div class="card-footer bg-transparent">
            {{ $academicYears->links() }}
        </div>

    @endif

</div>


{{-- ================= ADD MODAL ================= --}}
<div class="modal fade"
     id="addAcademicYearModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form method="POST"
                  action="{{ route('academic-years.store') }}">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-primary me-1"></i>
                        Add Academic Year
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        {{-- Academic Year --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Academic Year <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="academic_year"
                                   class="form-control"
                                   placeholder="e.g. 2026-2027"
                                   required>

                        </div>

                        {{-- Campus --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Campus <span class="text-danger">*</span>
                            </label>

                            <select name="campus_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Select Campus
                                </option>

                                @foreach($campuses as $campus)

                                    <option value="{{ $campus->id }}">
                                        {{ $campus->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- From --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                From <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                   name="from"
                                   class="form-control"
                                   required>

                        </div>

                        {{-- To --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                To <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                   name="to"
                                   class="form-control"
                                   required>

                        </div>

                        {{-- Course Category --}}
                        <div class="col-md-12">

                            <label class="form-label">
                                Course Category
                                <span class="text-muted small">(optional)</span>
                            </label>

                            <select name="course_category_id"
                                    class="form-select">

                                <option value="">
                                    None — set later
                                </option>

                                @foreach($categories as $category)

                                    <option value="{{ $category->id }}">
                                        {{ $category->name_en }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- Current Year --}}
                        <div class="col-md-12">

                            <div class="form-check form-switch">

                                <input type="checkbox"
                                       name="current_year"
                                       value="1"
                                       class="form-check-input"
                                       id="currentYear">

                                <label class="form-check-label"
                                       for="currentYear">
                                    Set as Current Academic Year
                                </label>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Save Academic Year
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
